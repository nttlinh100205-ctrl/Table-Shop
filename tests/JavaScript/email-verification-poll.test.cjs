const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('resources/views/components/email-verification-poll.blade.php','utf8')
    .replace(/@json\(route\('verification.status'\)\)/g, '"/email/verification-status"')
    .replace(/@json\(auth\(\)->id\(\)\)/g, '7');
const flush = () => new Promise(resolve => setImmediate(resolve));
function mount(responses) {
    const elements = {}, timers = new Map(), listeners = {}, redirects = [], requests = [];
    let timerId = 0;
    const document = {
        hidden: false,
        getElementById(id) { return elements[id] ||= {hidden:true, addEventListener(event, fn) { this[event] = fn; }}; },
        addEventListener(event, fn) { listeners[event] = fn; },
    };
    vm.runInNewContext(source, {
        document, AbortController,
        window:{location:{replace:url=>redirects.push(url)},addEventListener:(event, fn)=>listeners[event]=fn},
        setTimeout:(fn, delay)=>{const id=++timerId; timers.set(id,{fn,delay}); return id;},
        clearTimeout:id=>timers.delete(id),
        fetch:async(url, options)=>{
            requests.push({url,options});
            const response = responses.shift();
            if (response instanceof Error) throw response;
            return {ok:true,status:200,json:async()=>response,...response};
        },
    });
    return {elements,timers,listeners,redirects,requests,document,
        tick(){const entry=[...timers].find(([,value])=>value.delay===3000); assert.ok(entry); timers.delete(entry[0]); entry[1].fn();},
    };
}
(async () => {
    const app = mount([{user_id:7,verified:false},{user_id:7,verified:true,redirect:'/'}]);
    await flush();
    assert.equal(app.redirects.length,0);
    assert.equal(app.requests[0].options.cache,'no-store');
    app.document.hidden = true;
    app.tick(); await flush();
    assert.equal(app.requests.length,1,'Background tabs do not keep polling');
    app.document.hidden = false;
    app.listeners.visibilitychange(); await flush();
    assert.deepEqual(app.redirects,['/'],'Returning to desktop after phone verification enters the site');
    assert.equal(app.timers.size,0,'Stop polling after successful verification');

    const expired = mount([{ok:false,status:401}]);
    await flush();
    assert.equal(expired.elements['verification-login'].hidden,false);
    assert.equal(expired.timers.size,0);
    assert.deepEqual(expired.redirects,[]);

    const changed = mount([{user_id:8,verified:true,redirect:'/'}]);
    await flush();
    assert.deepEqual(changed.redirects,[],'A different account cannot complete this waiting page');
    assert.match(changed.elements['verification-status'].textContent,/thay đổi/);

    const retry = mount([new Error('offline'),{user_id:7,verified:true,redirect:'/'}]);
    await flush();
    retry.tick(); await flush();
    assert.deepEqual(retry.redirects,['/']);
    console.log('PASS: cross-device status redirects, background resume, expired sessions, changed accounts and connection retry.');
})().catch(error=>{console.error(error);process.exitCode=1;});
