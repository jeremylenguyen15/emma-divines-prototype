/* Emma Divines blog pages (article list + article). Settings come from window.ED (printed by the plugin). */
(()=>{
const ED=window.ED||{};
const $=(s,el=document)=>el.querySelector(s), $$=(s,el=document)=>[...el.querySelectorAll(s)];
const desktopMQ=matchMedia('(min-width:1025px)');
const track=(name,p={})=>{if(typeof window.gtag==='function')window.gtag('event',name,p);};

/* ---------- Header + mobile menu ---------- */
const hdr=$('#hdr');const onScroll=()=>hdr&&hdr.classList.toggle('scrolled',scrollY>40);addEventListener('scroll',onScroll,{passive:true});onScroll();
const mm=$('#mmenu');
function closeMenu(){if(!mm||mm.hidden)return;mm.hidden=true;$('#menu-open').setAttribute('aria-expanded','false');document.documentElement.style.overflow='';}
if(mm){
  $('#menu-open').addEventListener('click',()=>{mm.hidden=false;$('#menu-open').setAttribute('aria-expanded','true');document.documentElement.style.overflow='hidden';$('#menu-close').focus();});
  $('#menu-close').addEventListener('click',closeMenu);
  $$('#mmenu nav a').forEach(a=>a.addEventListener('click',closeMenu));
  addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu();});
}

/* ---------- Stars ---------- */
const LAYERS=[{kind:'dot',share:.55,min:1.2,max:2.4,o:[.25,.6]},{kind:'star',share:.33,min:3,max:5.5,o:[.35,.7]},{kind:'star',share:.12,min:6,max:9,o:[.55,.85]}];
$$('[data-stars]').forEach((el,si)=>{
  const [dn,mn]=el.dataset.stars.split(',').map(Number), top=+el.dataset.top, span=+el.dataset.span;
  let seed=11+si*7;const rnd=()=>{seed=(seed*9301+49297)%233280;return seed/233280;};
  const n=(desktopMQ.matches?dn:mn)*2;
  el.innerHTML=LAYERS.map(L=>{const cnt=Math.round(n*L.share);let h='';
    for(let i=0;i<cnt;i++){const sz=L.min+rnd()*(L.max-L.min);
      const st=`left:${(rnd()*100).toFixed(2)}%;top:${(top+rnd()*span).toFixed(2)}%;width:${sz.toFixed(1)}px;height:${sz.toFixed(1)}px;--o:${(L.o[0]+rnd()*(L.o[1]-L.o[0])).toFixed(2)};--t:${(5+rnd()*6).toFixed(2)}s;--d:${(-rnd()*10).toFixed(2)}s`;
      h+=L.kind==='dot'?`<i class="dot" style="${st}"></i>`:`<svg class="star" style="${st}"><use href="#sp"/></svg>`;}
    return `<div class="layer" style="top:0;height:100%">${h}</div>`;}).join('');
});

/* ---------- Topic chips (article list); /blog/#tarot opens on that topic ---------- */
const chips=$$('.chip');
function filter(cat){
  const chip=chips.find(c=>c.dataset.cat===cat)||chips[0];if(!chip)return;
  cat=chip.dataset.cat;
  chips.forEach(c=>c.setAttribute('aria-pressed',String(c===chip)));
  let shown=0;
  $$('.feat-card,.post-grid .post').forEach(p=>{const on=!cat||p.dataset.cat===cat;p.hidden=!on;if(on)shown++;});
  const empty=$('.empty-note');if(empty)empty.hidden=shown>0;
}
chips.forEach(c=>c.addEventListener('click',()=>{filter(c.dataset.cat);track('blog_filter',{topic:c.dataset.cat||'all'});
  history.replaceState(null,'',location.pathname+location.search+(c.dataset.cat?'#'+c.dataset.cat:''));}));
if(chips.length&&location.hash)filter(decodeURIComponent(location.hash.slice(1)));

/* ---------- Copy link (article) ---------- */
$$('.copy-link').forEach(b=>b.addEventListener('click',async()=>{
  const note=b.parentElement.querySelector('.copied');
  try{await navigator.clipboard.writeText(b.dataset.url);if(note)note.textContent='Link copied';}
  catch(_){if(note)note.textContent=b.dataset.url;}
  track('share',{method:'copy_link'});
}));

/* ---------- Contact form (footer) ---------- */
const form=$('#contact');
if(form)form.addEventListener('submit',async e=>{e.preventDefault();const f=e.target,note=$('#form-note'),btn=f.querySelector('button[type=submit]');
  if(!f.email.value.trim()||!f.msg.value.trim()){note.textContent='Please add your email and a short message.';return;}
  btn.disabled=true;note.textContent='Sending…';
  try{const r=await fetch(ED.ajax,{method:'POST',body:new FormData(f)});const d=await r.json();
    if(d&&d.success){f.reset();note.textContent=ED.thanks;track('generate_lead',{form:'contact',placement:ED.view});}else{note.textContent=(d&&d.data)||ED.formError;}}
  catch(_){note.textContent=ED.formError;}
  btn.disabled=false;});

/* ---------- Analytics: booking, phone, social and article clicks ---------- */
document.addEventListener('click',e=>{
  const a=e.target.closest('a[href]');if(!a)return;
  const href=a.getAttribute('href')||'';
  const where=a.closest('#hdr,#mmenu')?'header':a.closest('footer')?'footer':a.closest('.art-cta,.post-cta')?'book_card':a.closest('.share')?'share':'page';
  const placement=(ED.view==='post'?'article_':'blog_')+where;
  if(/^(tel|sms):/.test(href)){track('phone_click',{placement});return;}
  if(/calendly\.com/i.test(href)||href===ED.book){track('book_click',{session:'General booking',placement,link_url:href});return;}
  if(a.closest('.social')){track('social_click',{network:a.getAttribute('aria-label')||href,placement});return;}
  if(a.closest('.share')){track('share',{method:a.getAttribute('aria-label')||href});return;}
  if(a.closest('.post,.feat-card'))track('select_content',{content_type:'article',item_id:href,placement});
},true);
})();
