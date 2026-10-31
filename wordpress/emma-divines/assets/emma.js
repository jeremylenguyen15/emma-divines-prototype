/* Emma Divines homepage. Content comes from window.ED (printed by the plugin). */
(()=>{
const ED=window.ED||{},BOOK=ED.book;
// Sessions and prices
const OFFERS=ED.offers;
const SHORT=ED.short||{};
// Each session books straight into its own Calendly event
const bookUrl=o=>(o&&o.href)||BOOK;
// Google Analytics events: only sent when the site has a Measurement ID (Site content → Tracking)
const track=(name,p={})=>{if(typeof window.gtag==='function')window.gtag('event',name,p);};
const ROMAN=['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX'];
const ART_FROM=['#3A2140','#693F6A','#4A2A50','#693F6A'], ART_TO=['#A8638C','#CA93B3','#936A94','#C08977'];
const PAGE=4;

const $=(s,el=document)=>el.querySelector(s), $$=(s,el=document)=>[...el.querySelectorAll(s)];
const cardsEl=$('#cards'), offeringsEl=$('#offerings');
const reduce=matchMedia('(prefers-reduced-motion: reduce)'), desktopMQ=matchMedia('(min-width:1025px)');
// Capture mode (Figma capture or ?capture): render every section at rest so a static snapshot shows it all
// Always open at the top so the hero and card deal play: no scroll restoration, and drop any #section left in the address
if('scrollRestoration' in history)history.scrollRestoration='manual';
const startHash=location.hash.slice(1).toLowerCase();  // kept for deep links (see below)
if(location.hash){history.replaceState(null,'',location.pathname+location.search);}
const toTop=()=>scrollTo({top:0,left:0,behavior:'instant'});
toTop();addEventListener('DOMContentLoaded',toTop);addEventListener('load',toTop);addEventListener('pageshow',toTop);
// In-page links scroll smoothly without writing #section into the address
document.addEventListener('click',e=>{
  const a=e.target.closest('a[href^="#"]');if(!a||e.defaultPrevented)return;
  const id=a.getAttribute('href').slice(1);const t=id==='top'?document.body:document.getElementById(id);if(!t)return;
  e.preventDefault();if(id==='top')scrollTo({top:0,behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});else t.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});
});
const CAPTURE=/figmacapture|[?&]capture\b/.test(location.href);
if(CAPTURE){
  document.documentElement.classList.add('capture');
  // Figma's capture misreads <use> nested inside a <symbol>; write the card artwork out inline instead
  document.querySelectorAll('svg > use[href="#cardback"], svg > use[href="#cardfront"]').forEach(u=>{
    const sym=document.querySelector(u.getAttribute('href')), svg=u.parentNode;
    svg.setAttribute('viewBox',sym.getAttribute('viewBox'));svg.innerHTML=sym.innerHTML;
  });
}
const state={tab:Object.keys(OFFERS)[0],page:0,order:[],mode:null,busy:false};
const wait=ms=>new Promise(r=>setTimeout(r,ms));
{const y=$('#yr');if(y)y.textContent=new Date().getFullYear();}

/* ---------- Tabs ---------- */
$('#tabs').innerHTML=Object.keys(OFFERS).map(k=>`<button class="tab" role="tab" aria-selected="${k===state.tab}" data-tab="${k}"><span class="long">${k}</span><span class="short">${SHORT[k]||k}</span></button>`).join('');
function selectTab(k){if(!OFFERS[k])return;state.tab=k;state.page=0;$$('.tab').forEach(t=>t.setAttribute('aria-selected',t.dataset.tab===k));render();}
$('#tabs').addEventListener('click',e=>{const b=e.target.closest('.tab');if(b&&b.dataset.tab!==state.tab)selectTab(b.dataset.tab);});
$$('a[data-tab]').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();closeMenu();selectTab(a.dataset.tab);openCat(a.dataset.tab);}));
$$('a[data-sheet]').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();closeMenu();openCat(a.dataset.sheet);}));

/* ---------- Card markup ---------- */
function artHTML(i,h,o){
  if(o&&o.img)return `<div class="art art-img"${h?` style="flex:0 0 ${h}px"`:''}><img src="${o.img}" alt="" loading="lazy" draggable="false"></div>`;
  const spots=[[50,50,22],[74,26,10],[28,74,7],[80,72,5]].slice(0,[2,3,4,3][i%4]);
  return `<div class="art" style="background:linear-gradient(${ART_FROM[i%4]},${ART_TO[i%4]})${h?';flex:0 0 '+h+'px':''}"><svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
    <circle cx="50" cy="50" r="40" fill="none" stroke="#F6E4EC" stroke-width=".7" stroke-dasharray="212 40" transform="rotate(${100+i*23} 50 50)" opacity=".85"/>
    ${spots.map(([x,y,s],k)=>`<use href="#sp" x="${x-s/2}" y="${y-s/2}" width="${s}" height="${s}" style="color:#fff" opacity="${k?.8:1}"/>`).join('')}</svg></div>`;
}
function backHTML(o){return `<h3 class="name">${o.n}</h3><p class="meta">${o.m}</p><p class="desc">${o.t}</p><p class="price">${o.p}</p><a class="btn btn-book" href="${bookUrl(o)}" target="_blank" rel="noopener">Book this session <span class="arr">→</span></a>`;}
function cardHTML(o,i){
  return `<article class="card" tabindex="0" aria-label="${o.n}, ${o.m}, ${o.p}. Press to see details.">
   <div class="tilt"><div class="flip">
    <div class="face front"><div class="inner"><span class="num">${ROMAN[i]}</span>${o.start?'<span class="start-tag">✦ Start here</span>':''}${artHTML(i,0,o)}<h3 class="name">${o.n}</h3><p class="meta">${o.m}</p><p class="price">${o.p}</p></div></div>
    <div class="face back"><div class="inner">${backHTML(o)}<button type="button" class="turn">↺ Turn card back</button></div></div>
   </div></div></article>`;
}
function setFinal(c,x,y,r){c.style.setProperty('--final',`translate(${x}px,${y}px) rotate(${r}deg)`);c.style.setProperty('--r',r+'deg');}

/* ---------- Render: fan on desktop (dealt in sets of 4), swipe deck on phones ---------- */
function render(){
  const all=OFFERS[state.tab], desk=desktopMQ.matches;
  const start=desk?state.page*PAGE:0, list=desk?all.slice(start,start+PAGE):all, n=list.length;
  state.mode=desk?'fan':'deck';offeringsEl.dataset.mode=state.mode;
  cardsEl.innerHTML=list.map((o,i)=>cardHTML(o,start+i)).join('');
  const cards=[...cardsEl.children];
  cards.forEach((c,i)=>{
    const t=i-(n-1)/2, maxT=(n-1)/2||1;
    const step=n>1?Math.min(7,30/(n-1)):0, sp=n>1?Math.min(176,600/(n-1)):0;
    const r=t*step, fx=t*sp, fy=Math.pow(t/maxT,2)*30;
    setFinal(c,fx,fy,r);
    c.style.setProperty('--i',i);c.style.setProperty('--dx',(-fx)+'px');c.style.setProperty('--dy','70px');c.style.setProperty('--dr',(-r)+'deg');
    c.addEventListener('animationend',e=>{if(e.target===c&&(e.animationName==='deal'||e.animationName==='fade'))c.classList.add('dealt');});
  });
  if(!desk){state.order=cards.map((_,i)=>i);applyDeck(true);}
  // pager for tabs with more than four sessions
  const pages=Math.ceil(all.length/PAGE), pager=$('#pager');
  pager.hidden=!(desk&&pages>1);
  if(desk&&pages>1){
    const last=state.page===pages-1, nextCount=last?Math.min(PAGE,all.length):Math.min(PAGE,all.length-(state.page+1)*PAGE);
    $('#dots').innerHTML=Array.from({length:pages},(_,k)=>`<i class="${k===state.page?'on':''}"></i>`).join('');
    $('#pager-label').textContent=`Cards ${ROMAN[start]}–${ROMAN[start+n-1]} of ${ROMAN[all.length-1]}`;
    $('#pager-btn').innerHTML=`${last?'Deal the first':'Deal the next'} ${nextCount} <svg class="sp"><use href="#sp"/></svg>`;
  }
}
$('#pager-btn').addEventListener('click',()=>{const pages=Math.ceil(OFFERS[state.tab].length/PAGE);state.page=(state.page+1)%pages;render();});

const DECK_POS=[[0,0,0],[26,8,6],[-54,18,-11],[56,26,13]];
function applyDeck(initial){
  const cards=[...cardsEl.children], n=cards.length;
  state.order.forEach((idx,p)=>{const c=cards[idx];const [x,y,r]=DECK_POS[p]||[0,30,0];
    setFinal(c,x,y,r);c.style.zIndex=n-p;c.classList.toggle('is-front',p===0);c.classList.toggle('is-hidden',p>=4);
    if(initial){c.style.setProperty('--i',Math.min(n,4)-1-Math.min(p,3));c.style.setProperty('--dx',(-x)+'px');c.style.setProperty('--dy','60px');c.style.setProperty('--dr',(-r)+'deg');}});
}
// Deck card leaves to one side, then fades back in at the bottom of the deck
function flyOut(c,dir,dy=0){
  state.busy=true;c.classList.add('dealt');c.classList.remove('flipped');
  c.style.transition='translate .38s cubic-bezier(.3,.6,.4,1),rotate .38s ease-out,opacity .38s ease-in,scale .2s';
  c.style.scale='';c.style.translate=`${dir*Math.round(innerWidth*1.1)}px ${Math.round(dy*.4-20)}px`;c.style.rotate=`${dir*28}deg`;c.style.opacity='0';
  setTimeout(()=>{c.style.transition='none';c.style.translate='';c.style.rotate='';
    state.order.push(state.order.shift());applyDeck(false);
    requestAnimationFrame(()=>requestAnimationFrame(()=>{c.style.transition='';c.style.opacity='';state.busy=false;}));},400);
}
function nextCard(){
  if(state.mode!=='deck'||state.busy||cardsEl.children.length<2)return;
  flyOut(cardsEl.children[state.order[0]],-1,0);
}
$('#deck-next').addEventListener('click',nextCard);
function toggleFlip(card){const was=card.classList.contains('flipped');$$('.card.flipped',cardsEl).forEach(c=>c.classList.remove('flipped'));if(!was){card.classList.add('flipped');card._flipT=performance.now();}}
// Tapping a turned-over card books it (anywhere on it); only the "Turn card back" button turns it back.
// downT = what the finger/mouse actually went down on (pointer capture hides it from the click event).
let downT=null;cardsEl.addEventListener('pointerdown',e=>{downT=e.target;},true);
cardsEl.addEventListener('click',e=>{
  const t=(downT&&downT.isConnected?downT:e.target);downT=null;
  if(t.closest('a')||e.target.closest('a'))return;const c=t.closest('.card')||e.target.closest('.card');if(!c)return;
  const now=performance.now();if(now-(c._dragT||0)<400)return;
  if(c.classList.contains('flipped')&&!t.closest('.turn')){if(now-(c._flipT||0)>450)c.querySelector('.btn-book').click();return;}
  if(state.mode==='deck')return;toggleFlip(c);});
cardsEl.addEventListener('keydown',e=>{
  if((e.key==='Enter'||e.key===' ')&&e.target.classList.contains('card')){e.preventDefault();toggleFlip(e.target);}
  if(state.mode==='deck'&&e.key==='ArrowRight')nextCard();});
// Tinder-style drag: the front card sticks to the finger, flings away or springs back; a tap flips it
let drag=null;
cardsEl.addEventListener('dragstart',e=>e.preventDefault());
cardsEl.addEventListener('pointerdown',e=>{
  if(state.mode!=='deck'||state.busy||drag)return;
  const c=e.target.closest('.card.is-front');if(!c||e.target.closest('a'))return;
  const r=c.getBoundingClientRect(),now=performance.now();
  drag={c,t:e.target,id:e.pointerId,x0:e.clientX,y0:e.clientY,dx:0,dy:0,vx:0,lx:e.clientX,lt:now,moved:false,pivot:e.clientY<r.top+r.height/2?1:-1};
  c.classList.add('dealt');c.setPointerCapture(e.pointerId);c.style.transition='scale .2s';c.style.scale='1.03';
});
cardsEl.addEventListener('pointermove',e=>{
  if(!drag||e.pointerId!==drag.id)return;
  const now=performance.now();drag.dx=e.clientX-drag.x0;drag.dy=e.clientY-drag.y0;
  drag.vx=(e.clientX-drag.lx)/Math.max(1,now-drag.lt);drag.lx=e.clientX;drag.lt=now;
  if(Math.abs(drag.dx)>6||Math.abs(drag.dy)>6)drag.moved=true;
  drag.c.style.translate=`${drag.dx}px ${drag.dy*.35}px`;drag.c.style.rotate=`${(drag.dx*.07*drag.pivot).toFixed(2)}deg`;
});
function endDrag(e,cancelled){
  if(!drag||e.pointerId!==drag.id)return;const d=drag,c=d.c;drag=null;
  if(!cancelled&&!d.moved){c.style.transition='';c.style.scale='';c.style.translate='';c.style.rotate='';
    if(!c.classList.contains('flipped')||(d.t&&d.t.closest('.turn')))toggleFlip(c);return;}
  c._dragT=performance.now();
  const fling=Math.abs(d.dx)>90||(Math.abs(d.vx)>.45&&Math.abs(d.dx)>25);
  if(!cancelled&&fling&&cardsEl.children.length>1)return flyOut(c,Math.sign(d.dx||d.vx),d.dy);
  c.style.transition='translate .5s cubic-bezier(.2,1.5,.4,1),rotate .5s cubic-bezier(.2,1.5,.4,1),scale .25s';
  c.style.translate='0px 0px';c.style.rotate='0deg';c.style.scale='';
  setTimeout(()=>{if(drag&&drag.c===c)return;c.style.transition='';c.style.translate='';c.style.rotate='';},520);
}
cardsEl.addEventListener('pointerup',e=>endDrag(e,false));
cardsEl.addEventListener('pointercancel',e=>endDrag(e,true));
if(matchMedia('(hover:hover) and (pointer:fine)').matches){
  cardsEl.addEventListener('pointermove',e=>{const c=e.target.closest('.card');if(!c||reduce.matches)return;
    const r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height,t=c.firstElementChild;
    t.style.setProperty('--rx',((.5-y)*10).toFixed(2)+'deg');t.style.setProperty('--ry',((x-.5)*12).toFixed(2)+'deg');
    c.style.setProperty('--gx',(x*100).toFixed(1)+'%');c.style.setProperty('--gy',(y*100).toFixed(1)+'%');});
  cardsEl.addEventListener('pointerout',e=>{const c=e.target.closest('.card');if(!c||c.contains(e.relatedTarget))return;
    const t=c.firstElementChild;t.style.removeProperty('--rx');t.style.removeProperty('--ry');});
}

/* ---------- Draw a card: Ask the cards ---------- */
// The 22 Major Arcana, with a message per focus
const ARC=window.ED_ARC.map((c,i)=>Object.assign({},c,(ED.arcEdits||{})[i]||{}));  // Emma's edits from Tarot cards
const FOCI={
  love:{label:'Love & relationships',go:'Want clarity on this relationship? Emma suggests',sess:'Classic Reading'},
  career:{label:'Career & purpose',go:'Want to map out your next move? Emma suggests',sess:'Deep Dive'},
  healing:{label:'Healing & self',go:'Ready to feel lighter? Emma suggests',sess:'Reiki Full Session'},
  loved:{label:'A loved one who’s passed',go:'Want to hear from them? Emma suggests',sess:'Evidential Mediumship'},
  guide:{label:'Just guide me',go:'Want to go deeper? Emma suggests',sess:null}
};
const SESS=Object.values(OFFERS).flat().reduce((m,o)=>(m[o.n]=o,m),{});
const ARC_GRAD=[['#4A2A50','#A8638C'],['#3A2140','#936A94'],['#693F6A','#CA93B3'],['#2A1830','#693F6A']];
// Major Arcana art: assets/images/arcana/<NN>.jpg (Rider-Waite-Smith scenes in the offering-card style)
const arcImg=i=>`<img src="${ARC[i].img||ED.arcana+String(i).padStart(2,'0')+'.jpg'}" alt="${ARC[i].name} tarot card" draggable="false">`;
const arcSVG=i=>`<svg viewBox="0 0 100 100" fill="none" stroke="#FBE9E3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#FBE9E3" aria-hidden="true">${ARC[i].sym}</svg>`;
const drawEl={focus:$('#step-focus'),pick:$('#step-pick'),read:$('#step-read'),fan:$('#fan'),chips:$('#focusChips'),shuffle:$('#shuffleBtn')};
let drawFocus=null,deck=[],drawBusy=false;
drawEl.chips.innerHTML=Object.entries(FOCI).map(([k,f])=>`<button class="fchip" data-f="${k}" aria-pressed="false"><svg><use href="#sp"/></svg>${f.label}</button>`).join('');
drawEl.chips.addEventListener('click',e=>{const b=e.target.closest('.fchip');if(!b)return;drawFocus=b.dataset.f;
  $$('.fchip',drawEl.chips).forEach(c=>c.setAttribute('aria-pressed',c===b));setShuffleReady();});
// Shuffle button: Waiting (no focus yet) → Ready. A tap while waiting nudges the deck and the chips instead of doing nothing.
function setShuffleReady(){drawEl.shuffle.closest('.deck-stage').classList.add('ready');drawEl.shuffle.setAttribute('aria-disabled','false');drawEl.shuffle.querySelector('.sb-label').textContent='Shuffle the deck';}
function nudgeFocus(){const d=$('#deck');[d,drawEl.chips].forEach(el=>{el.classList.remove('nudge');void el.offsetWidth;el.classList.add('nudge');});drawEl.chips.querySelector('.fchip')?.focus({preventScroll:true});}
function tryShuffle(){if(drawFocus)dealFan();else nudgeFocus();}
$('#deck').addEventListener('click',tryShuffle);
function showStep(name){['focus','pick','read'].forEach(k=>{const el=drawEl[k];const on=k===name;el.hidden=!on;el.classList.toggle('enter',on&&!reduce.matches);});}
function layoutFan(){
  const fan=drawEl.fan,W=fan.clientWidth||900,n=deck.length;
  const cw=W<600?Math.max(44,Math.min(60,(W-20)/7)):Math.min(92,W/11.5),ch=cw*1.6,A=(W<600?36:38)*Math.PI/180,R=(W-cw-10)/(2*Math.sin(A)),top=56;
  fan.style.setProperty('--cw',cw+'px');fan.style.setProperty('--fh',(top+R*(1-Math.cos(A))+ch+10)+'px');
  [...fan.children].forEach((c,i)=>{const a=-A+2*A*i/(n-1);
    c.style.setProperty('--x',(W/2+R*Math.sin(a)-cw/2).toFixed(1)+'px');c.style.setProperty('--y',(top+R*(1-Math.cos(a))).toFixed(1)+'px');
    c.style.setProperty('--r',(a*180/Math.PI).toFixed(2)+'deg');c.style.setProperty('--x0',(W/2-cw/2).toFixed(1)+'px');c.style.setProperty('--i',i);});
}
function dealFan(){
  deck=ARC.map((_,i)=>i).sort(()=>Math.random()-.5);
  drawEl.fan.className='fan';
  drawEl.fan.innerHTML=deck.map((_,i)=>`<button class="fcard" aria-label="Card ${i+1} of 22"><svg><use href="#sp"/></svg></button>`).join('');
  $('#pickFor').innerHTML=`<svg><use href="#sp"/></svg>${FOCI[drawFocus].label}`;
  showStep('pick');layoutFan();
  if(!reduce.matches){drawEl.fan.classList.add('dealing');setTimeout(()=>drawEl.fan.classList.remove('dealing'),22*35+950);}
  drawBusy=false;
}
drawEl.shuffle.addEventListener('click',tryShuffle);
drawEl.fan.addEventListener('click',async e=>{
  const c=e.target.closest('.fcard');if(!c||drawBusy||drawEl.fan.classList.contains('dealing'))return;drawBusy=true;
  c.classList.add('chosen');drawEl.fan.classList.add('picked');await wait(reduce.matches?0:650);
  revealArcana(deck[[...drawEl.fan.children].indexOf(c)]);
});
function revealArcana(i){
  const a=ARC[i],f=FOCI[drawFocus],msg=drawFocus==='guide'?a.guide:a[drawFocus],o=SESS[f.sess||a.sess]||SESS[ED.fallback]||Object.values(SESS)[0],g=ARC_GRAD[i%4];
  track('draw_card',{card:a.name,focus:f.label,suggested_session:o&&o.n});
  drawEl.read.innerHTML=`<div class="rcard flipped" id="arcCard"><div class="flip">
      <div class="face front arc-face"><div class="inner"><span class="arc-num">${a.num}</span><div class="arc-art" style="background:linear-gradient(${g[0]},${g[1]})">${arcImg(i)}</div><p class="arc-name">${a.name}</p></div></div>
      <div class="face back rv-back"><div class="inner" style="padding:0;position:relative"><svg><use href="#cardback"/></svg><span class="cb-label">Emma Divines</span></div></div></div></div>
    <div class="reading">
      <span class="for-chip">For: ${f.label}</span>
      <h3>${a.num} · ${a.name}</h3>
      <p class="msg">“${msg}”</p>
      <p class="kw">${a.kw}</p>
      <hr>
      <p class="go">${f.go}</p>
      <div class="sess"><div><b>${o.n}</b><span>${o.m} · ${o.p}</span></div><a class="btn btn-purple" href="${bookUrl(o)}" target="_blank" rel="noopener">Book <span class="arr">→</span></a></div>
      <div class="read-links"><button class="link light js-again">Draw again <span class="arr">↻</span></button><button class="link light js-refocus">Choose another focus</button></div>
    </div>`;
  showStep('read');
  requestAnimationFrame(()=>requestAnimationFrame(()=>$('#arcCard').classList.remove('flipped')));
}
$('#drawStage').addEventListener('click',e=>{
  if(e.target.closest('.js-again')){dealFan();}
  if(e.target.closest('.js-refocus')){showStep('focus');}
});
addEventListener('resize',()=>{if(!drawEl.pick.hidden)layoutFan();});

/* ---------- Prices dialog ---------- */
const dlg=$('#prices');
$('#prices-body').innerHTML=Object.entries(OFFERS).map(([k,list])=>`<section class="grp"><h3>${k.toUpperCase()}</h3>${list.map(o=>
  `<div class="row"><div><b>${o.n}</b><small>${o.m}</small></div><span class="p">${o.p}</span><a href="${bookUrl(o)}" target="_blank" rel="noopener">Book</a></div>`).join('')}</section>`).join('')+
  `<p class="dlg-policy">${ED.policy}</p>`;
$$('.js-all').forEach(b=>b.addEventListener('click',()=>{dlg.showModal();track('view_price_list');}));
$('#dlg-close').addEventListener('click',()=>dlg.close());
dlg.addEventListener('click',e=>{if(e.target===dlg)dlg.close();});

/* ---------- Category sheet ---------- */
// Category sheets: intro + an illustration of Emma
const CAT=ED.cat;
const EVENTS=ED.events||[];
const catDlg=$('#cat');
function openCat(k){
  track('view_category',{category:k});
  const c=CAT[k],list=OFFERS[k]||(k===ED.eventsKey?EVENTS:null);if(!list)return;if(!c)return;
  catDlg.innerHTML=`<div class="cat-grid">
    <div class="cat-photo${c.gallery?' cat-gallery':''}">${c.gallery?c.gallery.map(g=>`<figure class="cg-tile"><img src="${g}" alt="" loading="lazy"></figure>`).join(''):c.photo?`<img src="${c.photo}" alt="${c.alt}">`:`<div class="cat-ph"><svg><use href="#sp"/></svg></div>`}</div>
    <div class="cat-body">
      <button class="close-btn cat-close" aria-label="Close">✕</button>
      <div class="cat-head"><p class="eyebrow"><svg><use href="#sp"/></svg>${c.eb}</p><h2 id="cat-title">${k}</h2><p>${c.intro}</p></div>
      <ul class="cat-list">${list.map((o,j)=>`${o.start&&j===0?'<li class="cat-group">Most people start here</li>':''}${o.quick&&!(list[j-1]||{}).quick?'<li class="cat-group">Quick check-ins</li>':''}<li class="cat-row"><div><b>${o.n}</b>${o.start?'<span class="start-tag inline">✦ Start here</span>':''}<span class="m">${o.m}</span></div>${o.p?`<span class="p">${o.p}</span>`:'<span></span>'}<a class="btn btn-purple" href="${bookUrl(o)}" target="_blank" rel="noopener">${o.cta||'Book'} <span class="arr">→</span></a><p class="t">${o.t}</p></li>`).join('')}</ul>${k===ED.eventsKey&&c.note?`<p class="policy">${c.note} <a href="${ED.tel}">Call to plan yours</a>.</p>`:''}
      <div class="cat-foot">${k===ED.eventsKey?'':`<p class="policy">${c.note||ED.policy}</p><p class="policy in-person">${ED.inPerson} <a href="${ED.tel}">Call to discuss</a>.</p>`}
        <div class="cat-links"><button class="link js-cat-all">See all sessions &amp; prices <span class="arr">→</span></button><a class="link" href="#draw">Not sure? Draw a card <svg class="sp"><use href="#sp"/></svg></a></div></div>
    </div></div>`;
  catDlg.querySelector('.cat-close').addEventListener('click',()=>catDlg.close());
  catDlg.querySelector('.js-cat-all').addEventListener('click',()=>{catDlg.close();dlg.showModal();});
  catDlg.querySelector('a[href="#draw"]')?.addEventListener('click',()=>catDlg.close());
  if(!catDlg.open)catDlg.showModal();catDlg.querySelector('.cat-body').scrollTop=0;
}
catDlg.addEventListener('click',e=>{if(e.target===catDlg)catDlg.close();});
// capture mode: ?sheet=Reiki opens a category sheet so it can be snapshotted
{const q=new URLSearchParams(location.search).get('sheet');if(q&&CAT[q])requestAnimationFrame(()=>openCat(q));}
// Deep links (the old pages redirect here): #readings, #mediumship, #reiki, #classes, #events open that sheet;
// #emma, #faq, #draw… scroll to the section once the page has settled at the top
if(startHash){
  const slug=t=>String(t).toLowerCase().replace(/&amp;/g,'and').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  addEventListener('load',()=>setTimeout(()=>{
    const key=Object.keys(OFFERS).find(k=>slug(k)===startHash||slug(SHORT[k]||'')===startHash);
    if(key){selectTab(key);openCat(key);return;}
    if(ED.eventsKey&&slug(ED.eventsKey)===startHash){openCat(ED.eventsKey);return;}
    const el=document.getElementById(startHash);if(el)el.scrollIntoView({behavior:reduce.matches?'instant':'smooth'});
  },400));
}

/* ---------- Header + mobile menu + form ---------- */
const hdr=$('#hdr');const onScroll=()=>hdr.classList.toggle('scrolled',scrollY>40);addEventListener('scroll',onScroll,{passive:true});onScroll();
const mm=$('#mmenu');
function closeMenu(){if(mm.hidden)return;mm.hidden=true;$('#menu-open').setAttribute('aria-expanded','false');document.documentElement.style.overflow='';}
$('#menu-open').addEventListener('click',()=>{mm.hidden=false;$('#menu-open').setAttribute('aria-expanded','true');document.documentElement.style.overflow='hidden';$('#menu-close').focus();});
$('#menu-close').addEventListener('click',closeMenu);
$$('#mmenu nav a').forEach(a=>a.addEventListener('click',closeMenu));
$$('.js-hello').forEach(a=>a.addEventListener('click',()=>setTimeout(()=>$('#f-name').focus({preventScroll:true}),reduce.matches?0:650)));
$('#contact').addEventListener('submit',async e=>{e.preventDefault();const f=e.target,note=$('#form-note'),btn=f.querySelector('button[type=submit]');
  if(!f.email.value.trim()||!f.msg.value.trim()){note.textContent='Please add your email and a short message.';return;}
  btn.disabled=true;note.textContent='Sending…';
  try{const r=await fetch(ED.ajax,{method:'POST',body:new FormData(f)});const d=await r.json();
    if(d&&d.success){f.reset();note.textContent=ED.thanks;track('generate_lead',{form:'contact'});}else{note.textContent=(d&&d.data)||ED.formError;}}
  catch(_){note.textContent=ED.formError;}
  btn.disabled=false;});

/* ---------- Analytics: booking, phone and social clicks ---------- */
document.addEventListener('click',e=>{
  const a=e.target.closest('a[href]');if(!a)return;
  const href=a.getAttribute('href')||'';
  const placement=a.closest('#step-read')?'draw_card':a.closest('#cat')?'category_sheet':a.closest('#prices')?'price_list':a.closest('.card')?'session_card':a.closest('#hdr,#mmenu')?'header':a.closest('footer')?'footer':(a.closest('section[id]')||{}).id||'page';
  if(/^(tel|sms):/.test(href)){track('phone_click',{placement});return;}
  if(/calendly\.com/i.test(href)||href===BOOK){
    const row=a.closest('.card,.cat-row,.row,.sess');const n=row&&row.querySelector('h3,b');
    track('book_click',{session:n?n.textContent.trim():'General booking',placement,link_url:href});return;}
  if(a.closest('.social'))track('social_click',{network:a.getAttribute('aria-label')||href,placement});
},true);

/* ---------- Stars: three depth layers, gentle twinkle ---------- */
// k = how much the layer lags the page on scroll (far dust lags most, so it reads as distant)
const LAYERS=[{k:.55,kind:'dot',share:.55,min:1.2,max:2.4,o:[.25,.6]},{k:.3,kind:'star',share:.33,min:3,max:5.5,o:[.35,.7]},{k:.12,kind:'star',share:.12,min:6,max:9,o:[.55,.85]}];
$$('[data-stars]').forEach((el,si)=>{
  const [dn,mn]=el.dataset.stars.split(',').map(Number), top=+el.dataset.top, span=+el.dataset.span;
  let seed=11+si*7;const rnd=()=>{seed=(seed*9301+49297)%233280;return seed/233280;};
  const n=(desktopMQ.matches?dn:mn)*2;
  el.innerHTML=LAYERS.map(L=>{
    const cnt=Math.round(n*L.share), extra=L.k*100;let h='';
    for(let i=0;i<cnt;i++){
      const sz=L.min+rnd()*(L.max-L.min), ys=top-extra*.5+rnd()*(span+extra*.5), y=(ys+extra)/(100+extra)*100;
      const st=`left:${(rnd()*100).toFixed(2)}%;top:${y.toFixed(2)}%;width:${sz.toFixed(1)}px;height:${sz.toFixed(1)}px;--o:${(L.o[0]+rnd()*(L.o[1]-L.o[0])).toFixed(2)};--t:${(5+rnd()*6).toFixed(2)}s;--d:${(-rnd()*10).toFixed(2)}s`;
      h+=L.kind==='dot'?`<i class="dot" style="${st}"></i>`:`<svg class="star" style="${st}"><use href="#sp"/></svg>`;}
    // the layer is taller than its section by k, so it never runs out of sky while it lags behind
    return `<div class="layer" data-k="${L.k}" style="top:${-extra}%;height:${100+extra}%">${h}</div>`;
  }).join('');
});

/* ---------- Breathing dusk-sky shader (tiny WebGL, no library) ---------- */
(()=>{
  const canvas=$('#aurora'), hero=$('.hero');
  const gl=canvas.getContext('webgl',{antialias:false,depth:false,powerPreference:'low-power',preserveDrawingBuffer:CAPTURE});
  if(!gl){canvas.remove();return;}
  const fs=`precision mediump float;
  uniform vec2 R;uniform float T;uniform float O;uniform vec3 G0,G1,G2,G3;uniform vec3 C[4];uniform vec2 P[4];uniform float A[4];uniform float S[4];
  float hs(vec2 p){p=fract(p*vec2(123.34,456.21));p+=dot(p,p+45.32);return fract(p.x*p.y);}
  float vn(vec2 p){vec2 i=floor(p),f=fract(p);vec2 u=f*f*(3.-2.*f);
    return mix(mix(hs(i),hs(i+vec2(1.,0.)),u.x),mix(hs(i+vec2(0.,1.)),hs(i+vec2(1.,1.)),u.x),u.y);}
  float fbm(vec2 p){float v=0.,a=.5;for(int i=0;i<4;i++){v+=a*vn(p);p=p*2.03+vec2(1.7,9.2);a*=.5;}return v;}
  void main(){vec2 uv=gl_FragCoord.xy/R;float asp=R.x/R.y;float y=uv.y;
    vec3 col=y<.33?mix(G0,G1,y/.33):y<.66?mix(G1,G2,(y-.33)/.33):mix(G2,G3,(y-.66)/.34);
    /* the original soft glows, now slowly breathing: each swells and fades on its own long cycle */
    for(int i=0;i<4;i++){float fi=float(i);
      vec2 p=P[i]+vec2(sin(T*(.16+.04*fi)+fi*1.7),cos(T*(.12+.05*fi)+fi))*.13;p.y-=O;
      float breathe=.62+.38*sin(T*(.5+.08*fi)+fi*1.9);
      vec2 d=uv-p;d.x*=asp;col=mix(col,C[i],exp(-dot(d,d)/(S[i]*(1.+.3*sin(T*(.42+.06*fi)+fi*2.3))))*A[i]*breathe);}
    /* faint nebula clouds drifting through the upper sky, in the same rose and mauve */
    vec2 q=vec2(uv.x*asp,uv.y+O)*1.8;
    vec2 w=vec2(fbm(q+vec2(T*.04,0.)),fbm(q+vec2(5.2,1.3)-vec2(0.,T*.03)));
    float n=fbm(q+1.8*w+vec2(T*.025,-T*.015));
    float cloud=smoothstep(.38,.82,n)*smoothstep(.25,.65,y+O)*(.6+.4*sin(T*.55));
    col=mix(col,mix(vec3(.792,.576,.702),vec3(.953,.788,.851),w.x),cloud*.42);   /* #CA93B3 → #F3C9D9 */
    /* the faint light band from before */
    float x=uv.x*asp;float bw=.74-O+.05*sin(x*2.6+T*.18)+.03*sin(x*6.1-T*.27);
    col+=vec3(.953,.788,.851)*exp(-pow((y-bw)/.07,2.))*(.55+.45*sin(x*3.7+T*.31))*.08;
    col=mix(col,G0,smoothstep(.08,0.,y));   /* land exactly on #DBB7AC so the hero melts into the next section */
    col+=(hs(gl_FragCoord.xy+fract(T))-.5)/160.;
    gl_FragColor=vec4(col,1.);}`;
  const sh=(t,src)=>{const s=gl.createShader(t);gl.shaderSource(s,src);gl.compileShader(s);return gl.getShaderParameter(s,gl.COMPILE_STATUS)?s:null;};
  const v=sh(gl.VERTEX_SHADER,'attribute vec2 p;void main(){gl_Position=vec4(p,0.,1.);}'),f=sh(gl.FRAGMENT_SHADER,fs);
  if(!v||!f){canvas.remove();return;}
  const prog=gl.createProgram();gl.attachShader(prog,v);gl.attachShader(prog,f);gl.linkProgram(prog);gl.useProgram(prog);
  gl.bindBuffer(gl.ARRAY_BUFFER,gl.createBuffer());gl.bufferData(gl.ARRAY_BUFFER,new Float32Array([-1,-1,3,-1,-1,3]),gl.STATIC_DRAW);
  const loc=gl.getAttribLocation(prog,'p');gl.enableVertexAttribArray(loc);gl.vertexAttribPointer(loc,2,gl.FLOAT,false,0,0);
  const U={};['R','T','O','G0','G1','G2','G3','C','P','A','S'].forEach(k=>U[k]=gl.getUniformLocation(prog,k));
  const hx=c=>[1,3,5].map(k=>parseInt(c.slice(k,k+2),16)/255);
  const G=['#DBB7AC','#A8638C','#693F6A','#2A1830'], GL=[['#CA93B3',.68,.5,.35,.1],['#C08977',.1,.12,.3,.1],['#F3C9D9',.85,.78,.12,.05],['#000000',0,0,0,.1]];
  ['G0','G1','G2','G3'].forEach((k,i)=>gl.uniform3fv(U[k],hx(G[i])));
  gl.uniform3fv(U.C,new Float32Array(GL.flatMap(g=>hx(g[0]))));gl.uniform2fv(U.P,new Float32Array(GL.flatMap(g=>[g[1],g[2]])));
  gl.uniform1fv(U.A,new Float32Array(GL.map(g=>g[3])));gl.uniform1fv(U.S,new Float32Array(GL.map(g=>g[4])));
  let raf=0,visible=true;const t0=performance.now(), SCALE=.35; // soft by nature: render small, let CSS upscale
  function frame(now,once){gl.uniform2f(U.R,canvas.width,canvas.height);gl.uniform1f(U.T,(now-t0)/1000*1.6+40.);gl.drawArrays(gl.TRIANGLES,0,3);
    if(once===true)return;raf=(!reduce.matches&&visible&&!document.hidden)?requestAnimationFrame(frame):0;}
  function start(){if(!raf&&!reduce.matches)raf=requestAnimationFrame(frame);}
  function resize(){const r=hero.getBoundingClientRect();canvas.width=Math.max(2,Math.round(r.width*SCALE));canvas.height=Math.max(2,Math.round(r.height*SCALE));gl.viewport(0,0,canvas.width,canvas.height);frame(performance.now(),true);}
  new ResizeObserver(resize).observe(hero);
  new IntersectionObserver(([e])=>{visible=e.isIntersecting;if(visible)start();}).observe(hero);
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)start();});
  reduce.addEventListener('change',()=>{if(!reduce.matches)start();});
  window.setAuroraOffset=o=>{gl.uniform1f(U.O,o);if(!raf)frame(performance.now(),true);};
  resize();start();
})();

/* ---------- Parallax: star layers and the aurora lag behind the page ---------- */
const para=$$('.layer').map(l=>({l,k:+l.dataset.k,host:l.closest('section,footer')}));
const heroEl=$('.hero');let pt=0;
function parallax(){
  pt=0;const vh=innerHeight;
  if(reduce.matches){para.forEach(p=>p.l.style.transform='');window.setAuroraOffset&&setAuroraOffset(0);return;}
  for(const p of para){const r=p.host.getBoundingClientRect();if(r.bottom<-80||r.top>vh+80)continue;
    p.l.style.transform=`translate3d(0,${(-r.top*p.k).toFixed(1)}px,0)`;}
  const hr=heroEl.getBoundingClientRect();
  if(window.setAuroraOffset&&hr.bottom>0)setAuroraOffset(-hr.top*.35/hr.height);
}
addEventListener('scroll',()=>{if(!pt)pt=requestAnimationFrame(parallax);},{passive:true});
addEventListener('resize',parallax);reduce.addEventListener('change',parallax);
parallax();

/* ---------- Google reviews (pulled from Emma's site's Trustindex widget) ---------- */
const REVIEWS=ED.reviews||[];
(()=>{const box=$('#revs');if(!box)return;
  box.innerHTML=REVIEWS.map(r=>`<article class="rev"><div class="stars-row" aria-label="5 out of 5 stars">★★★★★</div><blockquote></blockquote><button class="more" hidden>Read more</button><div class="who"><i aria-hidden="true">${r.n[0]}</i><span><b></b>Google review${r.d?' · '+r.d:''}</span></div></article>`).join('');
  [...box.children].forEach((el,i)=>{el.querySelector('blockquote').textContent=REVIEWS[i].t;el.querySelector('.who b').textContent=REVIEWS[i].n;});
  const fit=()=>[...box.children].forEach(el=>{const q=el.querySelector('blockquote');if(el.classList.contains('open'))return;el.querySelector('.more').hidden=q.scrollHeight<=q.clientHeight+2;});
  fit();addEventListener('resize',fit);document.fonts&&document.fonts.ready.then(fit);
  box.addEventListener('click',e=>{const b=e.target.closest('.more');if(!b)return;const c=b.closest('.rev');c.classList.toggle('open');b.textContent=c.classList.contains('open')?'Show less':'Read more';});
  const btns=$$('.rev-btn'),upd=()=>{btns[0].disabled=box.scrollLeft<4;btns[1].disabled=box.scrollLeft+box.clientWidth>=box.scrollWidth-4;};
  btns.forEach(b=>b.addEventListener('click',()=>{const card=box.firstElementChild;const step=card?card.getBoundingClientRect().width+24:box.clientWidth;box.scrollBy({left:+b.dataset.dir*step,behavior:reduce.matches?'auto':'smooth'});}));
  box.addEventListener('scroll',upd,{passive:true});upd();
})();

/* ---------- Shooting stars: one every 1–3.5s while the section is on screen ---------- */
function shooter(section){
  const host=$('.shoots',section);if(!host)return;let vis=false,tm=0;
  const schedule=ms=>{clearTimeout(tm);tm=setTimeout(go,ms??1200+Math.random()*2300);};
  function go(){
    tm=0;if(!vis||document.hidden||reduce.matches)return;
    const w=host.clientWidth,h=host.clientHeight,dir=Math.random()<.7?-1:1;
    const len=110+Math.random()*90, dx=dir*(320+Math.random()*320), dy=140+Math.random()*160;
    const x=dir<0?w*(.35+Math.random()*.6):w*(.05+Math.random()*.6), y=h*(.03+Math.random()*.3);
    const s=document.createElement('i');s.className='shoot';
    s.style.cssText=`left:${(x-len).toFixed(0)}px;top:${y.toFixed(0)}px;--len:${len.toFixed(0)}px;--a:${(Math.atan2(dy,dx)*180/Math.PI).toFixed(1)}deg;--dx:${dx.toFixed(0)}px;--dy:${dy.toFixed(0)}px;--dur:${(.9+Math.random()*.6).toFixed(2)}s`;
    s.addEventListener('animationend',()=>s.remove());host.appendChild(s);schedule();
  }
  new IntersectionObserver(([e])=>{vis=e.isIntersecting;if(vis){if(!tm)schedule(500+Math.random()*900);}else{clearTimeout(tm);tm=0;}}).observe(section);
}
if(!CAPTURE){shooter($('.hero'));shooter($('#draw'));}

/* ---------- Scroll reveals ---------- */
if('IntersectionObserver' in window&&!reduce.matches&&!CAPTURE){
  document.documentElement.classList.add('js-reveal');
  const els=$$('[data-reveal]');
  const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.remove('pre');io.unobserve(e.target);}}),{rootMargin:'0px 0px -8% 0px',threshold:.12});
  els.forEach(el=>{
    if(el.hasAttribute('data-stagger')){const sib=[...el.parentElement.children].filter(c=>c.hasAttribute('data-stagger'));el.style.setProperty('--rd',sib.indexOf(el)*90+'ms');}
    if(el.getBoundingClientRect().top<innerHeight*.92)return; // already on screen at load: leave it at rest
    el.classList.add('pre');io.observe(el);
  });
}

desktopMQ.addEventListener('change',()=>{state.page=0;render();});
render();
})();
