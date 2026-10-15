const art = [
 '<path fill="#ed6571" d="M22 38c0-18 52-18 52 0S56 83 48 83 22 56 22 38Z"/><path fill="#668b42" d="m48 17-7 14-15-5 10 13 12-8 12 8 10-13-15 5Z"/><path d="M48 10v15"/><g stroke="#923746"><path d="m35 43 2 3m20-3 2 3m-13 8 2 3m-12 4 2 3m19-2 2 3m-11 6 2 3"/></g>',
 '<ellipse cx="35" cy="48" rx="15" ry="25" fill="#c49263" transform="rotate(25 35 48)"/><path d="M32 29c-8 10-12 27-7 39m12-36c-6 10-10 25-6 34" stroke="#8b613e"/><ellipse cx="67" cy="54" rx="13" ry="21" fill="#e4bd81" transform="rotate(-20 67 54)"/><path d="m64 38 8 31m-13-25 9 5m-6 7 10 4" stroke="#ad804e"/>',
 '<path fill="#ffb536" d="M60 25c27 8 19 44-4 52-22 8-40-12-29-34 7-13 17-13 33-18Z"/><path fill="#88a44d" d="M53 25c-4-15-18-14-27-7 10 8 17 10 27 7Z"/><path d="M53 25c1-8 8-13 14-13"/><path d="M65 36c8 9 5 23-3 29" stroke="#fff0b5"/>',
 '<path fill="#d85c71" d="M48 16c-24-13-32 12-18 20-20 5-11 27 4 26 1 17 28 17 31 0 20-2 19-25 5-28 6-20-15-28-22-18Z"/><path d="M48 76v14m0-13L33 67" stroke="#638a42"/><path fill="#7b9b4f" d="M48 75c9-12 19-10 22-7-5 10-13 13-22 7Z"/><path d="M36 36c2-15 27-12 26 4-1 13-20 18-24 5-3-10 11-12 14-5" stroke="#9e324c"/>',
 '<path fill="#9eae65" d="M48 23c31 0 38 49 9 57-39 10-51-51-9-57Z"/><path d="M48 23 43 12m-7 26 10-7 11 7 12-4m-40 17 13-8 12 9 13-7m-29 20 11-9 14 9" stroke="#718248"/>',
 '<path fill="#fff4dc" d="M20 48c11-12 42-12 56 0l-9 29H29Z"/><path d="M20 48c12 10 43 10 56 0" stroke="#d3a775"/><path fill="#e3c18c" d="m31 40 8-13 16 2 9 14-15 7Z"/><path d="m37 33 14 6m-9-9 15 5" stroke="#ba915e"/>',
 '<ellipse cx="48" cy="67" rx="33" ry="12" fill="#e7c27c"/><ellipse cx="36" cy="53" rx="16" ry="10" fill="#fff3d0"/><ellipse cx="62" cy="53" rx="16" ry="10" fill="#fff3d0"/><path d="m28 53 8-2m18 3 10-3m-18 15 10-3" stroke="#85a157"/>',
 '<path fill="#875037" d="m24 21 51 11-14 53-51-14Z"/><g stroke="#c98c61"><path d="m40 25-14 53m31-49L43 81M20 38l51 11M15 55l51 11"/></g><path d="m61 70 17-18 9 12-16 22Z" fill="#d69784"/>',
 '<circle cx="48" cy="50" r="30" fill="#b6bd73"/><path d="M48 20c-22 15-23 44 0 60m0-60c22 15 23 44 0 60m0-60v60" stroke="#859557"/><path d="M48 20c1-7-5-11-11-9"/>',
 '<path fill="#9c6680" d="M48 22c0 12-25 21-25 42 0 27 50 27 50 0 0-21-25-30-25-42Z"/><path fill="#f6bdab" d="M48 38c-6 10-14 17-14 27 0 17 28 17 28 0 0-10-8-17-14-27Z"/><path d="m43 54 2 2m9 4-2 2m-10 5 2 2m9 3-2 2" stroke="#fff1cb"/>',
 '<ellipse cx="34" cy="49" rx="11" ry="21" fill="#c99769" transform="rotate(25 34 49)"/><ellipse cx="63" cy="57" rx="12" ry="18" fill="#a9ba76" transform="rotate(-25 63 57)"/><path d="m35 33-7 29m36-18-3 28" stroke="#71864b"/><g stroke="#e49229"><path d="m45 22 5 14m10-17-3 15m15-11-7 14"/></g>'
];
let dispose;
export function renderFlavourCarousel(host, flavours, n, lang) {
 dispose?.();
 const labels={en:['Previous flavours','Next flavours','Pause sliding','Resume sliding','Flavour carousel'],mr:['मागील चवी','पुढील चवी','स्लाइड थांबवा','स्लाइड सुरू करा','चवींचे पर्याय'],hi:['पिछले स्वाद','अगले स्वाद','स्लाइड रोकें','स्लाइड शुरू करें','स्वाद के विकल्प']}[lang];
 host.className='flavour-carousel'; host.setAttribute('role','region'); host.setAttribute('aria-label',labels[4]);
 const cards=flavours.map((f,i)=>`<div class="flavour-slide" tabindex="0"><div class="ingredient-art"><svg viewBox="0 0 96 96" fill="none" stroke="#67482f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${art[i]}</svg></div><b>${f[n+1]}</b><span class="flavour-accent" aria-hidden="true"></span></div>`).join('');
 host.innerHTML=`<div class="flavour-window"><div class="flavour-track"><div class="flavour-group">${cards}</div><div class="flavour-group" aria-hidden="true" inert>${cards}</div></div></div><div class="carousel-controls"><button type="button" class="carousel-prev" aria-label="${labels[0]}">←</button><button type="button" class="carousel-pause" aria-label="${labels[2]}" aria-pressed="false">Ⅱ</button><button type="button" class="carousel-next" aria-label="${labels[1]}">→</button></div>`;
 const track=host.querySelector('.flavour-track'), group=host.querySelector('.flavour-group'), viewport=host.querySelector('.flavour-window');
 const reduced=matchMedia('(prefers-reduced-motion: reduce)');
 let offset=0,last=0,raf,hover=false,focus=false,paused=false,visible=false,startX=null;
 const paint=()=>{const width=group.getBoundingClientRect().width;if(width)offset=(offset%width+width)%width;track.style.transform=`translate3d(${-offset}px,0,0)`};
 const tick=t=>{if(last&&!hover&&!focus&&!paused&&visible&&!document.hidden&&!reduced.matches)offset+=Math.min(t-last,64)*.018;last=t;paint();raf=requestAnimationFrame(tick)};
 host.onmouseenter=()=>hover=true;host.onmouseleave=()=>hover=false;
 host.onfocusin=e=>{focus=true;if(e.target.classList.contains('flavour-slide')){const card=e.target.getBoundingClientRect(),windowRect=viewport.getBoundingClientRect();if(card.left<windowRect.left||card.right>windowRect.right){offset=e.target.offsetLeft;paint()}}};host.onfocusout=e=>{focus=host.contains(e.relatedTarget)};
 const move=direction=>{offset+=direction*(host.querySelector('.flavour-slide').getBoundingClientRect().width+18);paint()};
 host.querySelector('.carousel-prev').onclick=()=>move(-1);host.querySelector('.carousel-next').onclick=()=>move(1);
 host.querySelector('.carousel-pause').onclick=e=>{paused=!paused;e.currentTarget.textContent=paused?'▷':'Ⅱ';e.currentTarget.setAttribute('aria-pressed',String(paused));e.currentTarget.setAttribute('aria-label',labels[paused?3:2])};
 viewport.onpointerdown=e=>{if(e.pointerType==='touch')startX=e.clientX};viewport.onpointerup=e=>{if(startX!==null&&Math.abs(e.clientX-startX)>25)move(e.clientX<startX?1:-1);startX=null};viewport.onpointercancel=()=>startX=null;
 const observer=new IntersectionObserver(entries=>visible=entries[0].isIntersecting);observer.observe(host);
 raf=requestAnimationFrame(tick);dispose=()=>{cancelAnimationFrame(raf);observer.disconnect()};
}

