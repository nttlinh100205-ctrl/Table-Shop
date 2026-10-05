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

    public static function search(string $message, ?array $behavior = null, array $history = []): array
    {
        $text = self::normalize($message);
        $previous = '';
        foreach (array_reverse($history) as $entry) {
            if (($entry['role'] ?? '') === 'user') { $previous = self::normalize($entry['text']); break; }
        }
        // Explicitly switching product type starts a new search; short follow-ups retain context.
        $types = self::phrases($text, ['ban', 'ghe', 'sofa', 'giuong']);
        if ($types) $previous = '';
        else $types = self::phrases($previous, ['ban', 'ghe', 'sofa', 'giuong']);
        $colors = self::phrases($text, self::COLORS) ?: self::phrases($previous, self::COLORS);
        $styles = self::phrases($text, self::STYLES) ?: self::phrases($previous, self::STYLES);
        $dimensions = self::dimensions($text) ?: self::dimensions($previous);
        $stop = explode(' ', 'toi minh ban can muon tim shop co khong a nhe cho voi la va hay loai mau kieu phong cach kich thuoc size sz san pham tu van giup duoc nao nay kia mot chiec gom thi con hon xin chao');
        $tokens = array_values(array_diff(array_unique(preg_split('/[^a-z0-9]+/', $text.' '.$previous)), $stop, ['']));
        $tokens = array_slice(array_filter($tokens, fn($t)=>strlen($t)>1 && !is_numeric($t)), 0, 24);
        $best = [];
        Product::with(['category', 'variants'])->chunkById(100, function ($products) use (&$best, $tokens, $colors, $styles, $dimensions, $behavior, $types) {
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
                usort($variants, fn($a,$b)=>($b['matches_detected_filters'] <=> $a['matches_detected_filters']) ?: ($b['_score'] <=> $a['_score']));
                $hasMatch = collect($variants)->contains('matches_detected_filters', true);
                $score += max(array_column($variants, '_score'));
                // No lexical hit and no detected filters: prefer browsing context only for generic questions.
                $hasFilters = $colors || $styles || $dimensions;
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
                    'style'=>$product->style, 'material'=>$product->material,
                    'description'=>Str::limit(strip_tags($product->description ?? ''), 300),
                    'price_vnd'=>(float)$product->price, 'url'=>route('products.show', $product->id),
                    'matches_detected_filters'=>$hasMatch, 'variants'=>$selected, '_score'=>$score];
            }
            usort($best, fn($a,$b)=>($b['matches_detected_filters'] <=> $a['matches_detected_filters']) ?: ($b['_score'] <=> $a['_score']) ?: ($a['id'] <=> $b['id']));
            $best = array_slice($best, 0, 8);
        });
        foreach ($best as &$product) unset($product['_score']);
        return ['detected_filters'=>['colors'=>$colors,'styles'=>$styles,'dimensions_cm'=>$dimensions], 'products'=>$best];
    }
}
