<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class ChatProductSearch
{
    private const COLORS = ['trang', 'den', 'nau', 'be', 'xam', 'xanh', 'do', 'vang', 'hong', 'kem'];
    private const STYLES = ['hien dai', 'toi gian', 'co dien', 'tan co dien', 'bac au', 'japandi', 'vintage', 'industrial', 'luxury'];

    private static function normalize(?string $value): string
    {
        $text = strtolower(Str::ascii(html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES, 'UTF-8')));
        $aliases = ['minimalist'=>'toi gian', 'minimalism'=>'toi gian', 'modern'=>'hien dai', 'scandinavian'=>'bac au', 'nordic'=>'bac au', 'white'=>'trang', 'black'=>'den', 'brown'=>'nau', 'grey'=>'xam', 'gray'=>'xam', 'beige'=>'be', 'ban lam viec'=>'ban van phong'];
        foreach ($aliases as $from => $to) $text = preg_replace('/\b'.preg_quote($from, '/').'\b/', $to, $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private static function phrases(string $text, array $phrases): array
    {
        return array_values(array_filter($phrases, fn($phrase) => preg_match('/\b'.preg_quote($phrase, '/').'\b/', $text)));
    }

    private static function dimensions(string $text): array
    {
        // Database dimensions are centimetres: 1m2, 1.2m and 120cm mean 120cm.
        $text = preg_replace_callback('/\b(\d+)m(\d{1,2})\b/', fn($m) => (string)(((float)$m[1] + (float)('0.'.$m[2])) * 100).'cm', $text);
        $text = preg_replace_callback('/\b(\d+(?:[.,]\d+)?)\s*(mm|cm|m)\b/', function ($m) {
            $n = (float)str_replace(',', '.', $m[1]);
            return (string)($m[2] === 'm' ? $n * 100 : ($m[2] === 'mm' ? $n / 10 : $n)).'cm';
        }, $text);
        if (preg_match('/\b(\d+(?:\.\d+)?)\s*(?:cm)?\s*[x×*]\s*(\d+(?:\.\d+)?)(?:\s*(?:cm)?\s*[x×*]\s*(\d+(?:\.\d+)?))?/', $text, $m)) {
            return ['width'=>(float)$m[1], 'depth'=>(float)$m[2]] + (isset($m[3]) ? ['height'=>(float)$m[3]] : []);
        }
        if (preg_match('/\b(cao|sau|rong|dai|size|sz)\s*(\d+(?:\.\d+)?)(?:\s*cm)?\b/', $text, $m)) {
            return [match($m[1]) {'cao'=>'height', 'sau'=>'depth', default=>'width'} => (float)$m[2]];
        }
        if (preg_match('/\b(\d+(?:\.\d+)?)\s*cm\b/', $text, $m)) return ['width'=>(float)$m[1]];
        return [];
    }

    private static function money(string $number, string $unit): float
    {
        if (in_array($unit, ['trieu','tr','k','nghin','ngan'], true)) {
            return (float)str_replace(',', '.', $number) * (in_array($unit, ['trieu','tr'], true) ? 1000000 : 1000);
        }
        return (float)str_replace(['.', ','], '', $number);
    }

    public static function budget(string $message): ?array
    {
        $text = self::normalize($message);
        $number = '(\d+(?:[.,]\d+)*)';
        $unit = '(trieu|tr|nghin|ngan|k|vnd|dong|d)';
        if (preg_match('/\b(?:tu\s+)?'.$number.'\s*'.$unit.'?\s*(?:den|toi|-)\s*'.$number.'\s*'.$unit.'\b/', $text, $m)) {
            $min = self::money($m[1], $m[2] ?: $m[4]);
            $max = self::money($m[3], $m[4]);
            return ['min'=>min($min,$max),'max'=>max($min,$max),'max_exclusive'=>false,'min_exclusive'=>false,'approximate'=>false];
        }
        if (!preg_match('/\b'.$number.'\s*'.$unit.'\b/', $text, $m)) return null;
        $value = self::money($m[1],$m[2]);
        if (preg_match('/\b(duoi|it hon|khong qua|toi da|tro xuong)\b/', $text)) {
            return ['min'=>0,'max'=>$value,'max_exclusive'=>(bool)preg_match('/\b(duoi|it hon)\b/',$text),'min_exclusive'=>false,'approximate'=>false];
        }
        if (preg_match('/\b(tren|tu|toi thieu|tro len)\b/', $text)) {
            return ['min'=>$value,'max'=>null,'max_exclusive'=>false,'min_exclusive'=>(bool)preg_match('/\btren\b/',$text),'approximate'=>false];
        }
        // "Tầm/khoảng" uses a disclosed +/-20% range, rather than exact equality.
        return ['min'=>$value * .8,'max'=>$value * 1.2,'max_exclusive'=>false,'min_exclusive'=>false,'approximate'=>true];
    }

    public static function criteria(string $message, array $history = [], array $saved = []): array
    {
        $state = $saved + ['types'=>[], 'colors'=>[], 'styles'=>[], 'dimensions_cm'=>[], 'budget_vnd'=>null];
        $turns = $saved ? [] : array_column(array_filter($history, fn($entry)=>($entry['role'] ?? '') === 'user'), 'text');
        $turns[] = $message;
        foreach ($turns as $turn) {
            $text = self::normalize($turn);
            if (self::isNamedProductReset($turn)) $state = ['types'=>[], 'colors'=>[], 'styles'=>[], 'dimensions_cm'=>[], 'budget_vnd'=>null];
            // Explicitly removing filters must not leave old constraints in the session.
            if (preg_match('/\b(bo|xoa|khong gioi han|khong can)\b/', $text)) {
                foreach (['budget_vnd'=>'gia|ngan sach', 'colors'=>'mau', 'styles'=>'phong cach', 'dimensions_cm'=>'size|sz|kich thuoc'] as $field=>$words) {
                    if (preg_match('/\b('.$words.')\b/', $text)) $state[$field] = $field === 'budget_vnd' ? null : [];
                }
            }
            $types = self::phrases($text, ['ban','ghe','sofa','giuong']);
            if ($types && $state['types'] && $types !== $state['types']) {
                $state = ['types'=>[], 'colors'=>[], 'styles'=>[], 'dimensions_cm'=>[], 'budget_vnd'=>null];
            }
            // "đến" in a budget range is not the colour "đen".
            $attributes = preg_replace('/(\d[\d.,]*\s*(?:trieu|tr|nghin|ngan|k)?)\s+den(?=\s+\d)/', '$1 toi', $text);
            foreach (['types'=>$types, 'colors'=>self::phrases($attributes,self::COLORS), 'styles'=>self::phrases($text,self::STYLES), 'dimensions_cm'=>self::dimensions($text), 'budget_vnd'=>self::budget($turn)] as $field=>$value) {
                if ($value) $state[$field] = $value;
            }
        }
        return $state;
    }

    public static function isNamedProductReset(string $message): bool
    {
        $text = self::normalize($message);
        if (!preg_match('/^(?:bo|xoa) (?:gioi han )?(?:gia|ngan sach|mau|kich thuoc|phong cach)(?: va (?:gia|mau|kich thuoc|phong cach))*[,;]? (?:cho toi xem|xem|tim) (.+?)[?.!]*$/', $text, $match)) return false;
        // Only allow an exact catalog name after a filter-reset request, never arbitrary instructions.
        return Product::select('name')->get()->contains(fn($product)=>self::normalize($product->name) === trim($match[1]));
    }

    public static function attributeReply(string $message, array $history, ?array $behavior): ?string
    {
        if (!self::isAttributeFollowUp($message, $history, $behavior)) return null;
        $catalog = self::search($message, $behavior, $history);
        $rows = [];
        foreach ($catalog['products'] as $product) {
            foreach ($product['variants'] as $variant) {
                if (!$variant['matches_detected_filters'] || $variant['stock'] === 0) continue;
                $dimensions = implode(' × ', array_filter($variant['dimensions_cm'], fn($n)=>$n !== null));
                $rows[] = '['.$product['name'].']('.$product['url'].') — '.number_format($variant['price_vnd'], 0, ',', '.').'đ; '.($variant['color'] ?: 'chưa ghi màu').'; '.($dimensions ? $dimensions.' cm' : ($variant['size_label'] ?: 'chưa ghi kích thước')).'; '.($variant['stock'] === null ? 'cần xác nhận tồn kho' : 'còn '.$variant['stock'].' sản phẩm').'.';
                break;
            }
            if (count($rows) === 3) break;
        }
        return $rows ? "Các mẫu khớp điều kiện hiện tại:\n".implode("\n\n", $rows) : null;
    }

    public static function isAttributeFollowUp(string $message, array $history, ?array $behavior): bool
    {
        $previous = self::criteria('', $history, $behavior['chat_search_context'] ?? []);
        $text = self::normalize($message);
        // Everyday shorthand must not turn a valid shopping follow-up into OFF_TOPIC.
        // Do not replace "k" globally: it is also the thousand-VND price unit.
        $text = preg_replace('/\b(?:ko|k0|hok|khum)\b/', 'khong', $text);
        $text = preg_replace('/\bk\s*([?!.,]*)$/', 'khong$1', $text);
        $types = self::phrases($text, ['ban','ghe','sofa','giuong']);
        if (!array_filter($previous) && !$types && empty($behavior['last_product_id'])) return false;
        if (!self::phrases($text, array_merge(self::COLORS, self::STYLES)) && !self::dimensions($text)) return false;
        // Consume only a small shopping grammar. Arbitrary instructions and mixed requests fail closed.
        $text = preg_replace('/(?<=\d)[x×*](?=\d)/', ' ', $text);
        $text = preg_replace('/\b\d+m\d{1,2}\b|\b\d+(?:[.,]\d+)?\s*(?:mm|cm|m)?\b/', ' ', $text);
        foreach (array_merge(self::STYLES, self::COLORS, ['ban van phong','ban tra','ban an','ban cafe','ban','ghe','sofa','giuong','san pham','phong cach','kich thuoc','thi sao','co','mau','dai','rong','cao','sau','size','sz','khong','nhe','a','con','doi','sang','va','loai','kieu','toi','minh','muon','can','tim','cho','xem','giup','shop','nao','nay','do','duoc']) as $word) {
            $text = preg_replace('/\b'.preg_quote($word, '/').'\b/', ' ', $text);
        }
        return trim(preg_replace('/[\s?!.,×*x-]+/', '', $text)) === '';
    }

    public static function isBudgetFollowUp(string $message, array $history, ?array $behavior): bool
    {
        if (!self::budget($message)) return false;
        $previous = self::criteria('', $history, $behavior['chat_search_context'] ?? []);
        if (!$previous['types'] && !$previous['budget_vnd'] && !$previous['styles'] && !$previous['colors']) return false;
        // A narrow fallback for pure budget changes, never for mixed requests or instructions.
        $text = preg_replace('/\b\d+(?:[.,]\d+)*\s*(?:trieu|tr|nghin|ngan|k|vnd|dong|d)?\b/', ' ', self::normalize($message));
        $text = preg_replace('/\b(?:thi sao|the nao|khong qua|it hon|toi da|toi thieu|tro xuong|tro len|ngan sach|duoi|tren|tam|khoang|tu|den|toi|gia|muc|con|nhe|a|thoi|sao|thi)\b/', ' ', $text);
        return trim(preg_replace('/[\s?!.,-]+/', '', $text)) === '';
    }

    public static function search(string $message, ?array $behavior = null, array $history = []): array
    {
        $text = self::normalize($message);
        $previous = '';
        foreach (array_reverse($history) as $entry) {
            if (($entry['role'] ?? '') === 'user') { $previous = self::normalize($entry['text']); break; }
        }
        $criteria = self::criteria($message, $history, $behavior['chat_search_context'] ?? []);
        $types = $criteria['types']; $colors = $criteria['colors']; $styles = $criteria['styles'];
        $dimensions = $criteria['dimensions_cm']; $budget = $criteria['budget_vnd'];
        if (self::phrases($text, ['ban','ghe','sofa','giuong'])) $previous = '';
        $stop = explode(' ', 'toi minh ban can muon tim shop co khong a nhe cho voi la va hay loai mau kieu phong cach kich thuoc size sz san pham tu van giup duoc nao nay kia mot chiec gom thi con hon xin chao gia tam khoang duoi tren trieu tr nghin ngan vnd dong den toi da sao the');
        $tokens = array_values(array_diff(array_unique(preg_split('/[^a-z0-9]+/', $text.' '.$previous)), $stop, ['']));
        $tokens = array_slice(array_filter($tokens, fn($t)=>strlen($t)>1 && !is_numeric($t)), 0, 24);
        $best = [];
        Product::with(['category', 'variants'])->chunkById(100, function ($products) use (&$best, $tokens, $colors, $styles, $dimensions, $behavior, $types, $budget) {
            foreach ($products as $product) {
                $name = self::normalize($product->name.' '.$product->category?->name.' '.$product->sku);
                if ($types && !self::phrases($name, $types)) continue;
                $attributes = self::normalize($product->style.' '.$product->material.' '.$product->description);
                $score = 0;
                foreach ($tokens as $token) {
                    if (self::phrases($name, [$token])) $score += 8;
                    if (self::phrases($attributes, [$token])) $score += 3;
                }
                $styleMatch = !$styles || count(self::phrases(self::normalize($product->style.' '.$product->description), $styles)) === count($styles);
                $variants = [];
                $sources = $product->variants->isEmpty() ? collect([$product]) : $product->variants;
                foreach ($sources as $variant) {
                    $price = (float)$variant->price;
                    if ($budget && ($price < $budget['min'] || ($budget['min_exclusive'] && $price <= $budget['min'])
                        || ($budget['max'] !== null && ($price > $budget['max'] || ($budget['max_exclusive'] && $price >= $budget['max']))))) continue;
                    $color = $variant->color ?: $product->color;
                    $variantText = self::normalize($color.' '.$variant->size_label.' '.$variant->sku);
                    $variantScore = 0;
                    foreach ($tokens as $token) if (self::phrases($variantText, [$token])) $variantScore += 5;
                    $colorMatch = !$colors || count(self::phrases(self::normalize($color), $colors)) === count($colors);
                    $actualDimensions = [];
                    $labelDimensions = self::dimensions(self::normalize($variant->size_label));
                    foreach (['width','depth','height'] as $axis) $actualDimensions[$axis] = $variant->$axis !== null ? (float)$variant->$axis : ($labelDimensions[$axis] ?? null);
                    $sizeMatch = true;
                    foreach ($dimensions as $axis=>$value) {
                        if ($actualDimensions[$axis] === null || abs($actualDimensions[$axis] - $value) > 0.1) $sizeMatch = false;
                    }
                    $match = $colorMatch && $sizeMatch && $styleMatch;
                    $variants[] = ['id'=>$variant->id, 'sku'=>$variant->sku, 'color'=>$color, 'size_label'=>$variant->size_label,
                        'dimensions_cm'=>$actualDimensions, 'price_vnd'=>(float)$variant->price,
                        'stock'=>$variant instanceof \App\Models\ProductVariant ? (int)$variant->stock : null,
                        'matches_detected_filters'=>$match, '_score'=>$variantScore + ($match ? 30 : 0)];
                }
                if (!$variants) continue;
                usort($variants, fn($a,$b)=>($b['matches_detected_filters'] <=> $a['matches_detected_filters']) ?: ($b['_score'] <=> $a['_score']));
                $hasMatch = collect($variants)->contains('matches_detected_filters', true);
                $score += max(array_column($variants, '_score'));
                // No lexical hit and no detected filters: prefer browsing context only for generic questions.
                $hasFilters = $colors || $styles || $dimensions || $budget;
                $lexicalScore = $score - ($hasMatch ? 30 : 0);
                if ($tokens && !$hasFilters && $lexicalScore <= 0) continue;
                if ($hasFilters && !$hasMatch && $lexicalScore <= 0) continue;
                $score += ($hasFilters && $hasMatch) ? 100 : 0;
                if (($behavior['category_id'] ?? null) == $product->category_id) $score += 1;
                if (isset($behavior['last_product_price'])) {
                    $score += 0.5 / (1 + abs((float)$product->price - (float)$behavior['last_product_price']) / 1000000);
                }
                $selected = array_slice($variants, 0, 5);
                foreach ($selected as &$variant) unset($variant['_score']);
                unset($variant);
                $best[] = ['id'=>$product->id, 'name'=>$product->name, 'category'=>$product->category?->name,
                    'style'=>$product->style, 'material'=>$product->material, 'warranty'=>$product->warranty,
                    'description'=>Str::limit(strip_tags($product->description ?? ''), 300),
                    'price_vnd'=>(float)$product->price, 'url'=>route('products.show', $product->id),
                    'matches_detected_filters'=>$hasMatch, 'variants'=>$selected, '_score'=>$score];
            }
            usort($best, fn($a,$b)=>($b['matches_detected_filters'] <=> $a['matches_detected_filters']) ?: ($b['_score'] <=> $a['_score']) ?: ($a['id'] <=> $b['id']));
            $best = array_slice($best, 0, 8);
        });
        foreach ($best as &$product) unset($product['_score']);
        return ['detected_filters'=>$criteria, 'products'=>$best];
    }

    public static function ensureProductLinks(string $reply, string $message, ?array $behavior, array $history): string
    {
        $text = self::normalize($reply);
        foreach (self::search($message, $behavior, $history)['products'] as $product) {
            if (str_contains($text, self::normalize($product['name'])) && !preg_match('~'.preg_quote($product['url'], '~').'(?=[)\s?#]|$)~', $reply)) {
                $reply .= "\n\n[Xem sản phẩm: ".$product['name'].']('.$product['url'].')';
            }
        }
        return $reply;
    }
}
