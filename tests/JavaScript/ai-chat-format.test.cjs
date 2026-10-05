const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('resources/views/components/ai-chat.blade.php', 'utf8');
const helper = source.slice(source.indexOf('function readableAiText('), source.indexOf('function renderAiMessages('));
const context = vm.createContext({});
vm.runInContext(helper, context);
const format = context.readableAiText;
const reply = format(`**Mẫu phù hợp**
| # | Tên sản phẩm | Giá | Link |
|---|---|---|---|
| 1 | **Bàn đen** | 4.000.000đ | [Chi tiết](https://shop.test/products/5) |
| 2 | Bàn trắng | 5.000.000đ | [Chi tiết](https://shop.test/products/8) |

> Còn hàng.`);
assert.ok(reply.includes('Tên sản phẩm: Bàn đen\nGiá: 4.000.000đ'));
assert.ok(reply.includes('[Chi tiết](https://shop.test/products/5)'));
assert.ok(reply.includes('Tên sản phẩm: Bàn trắng'));
assert.ok(!reply.includes('|'));
assert.ok(!reply.includes('**'));
assert.equal(format('Bàn 140 × 70 cm\n[Chi tiết](https://shop.test/products/5)'), 'Bàn 140 × 70 cm\n[Chi tiết](https://shop.test/products/5)');
assert.equal(format('<script>alert(1)</script>'), '<script>alert(1)</script>'); // Renderer retains textContent, never innerHTML.
console.log('PASS: chat tables become readable cards, links and plain text remain intact.');
