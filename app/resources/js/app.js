import { renderHome } from './home';

let locale = ['mr','hi','en'].includes(document.documentElement.lang) ? document.documentElement.lang : 'mr';
function translate() {
 document.documentElement.lang=locale;
 document.body.classList.remove('mr','hi','en'); document.body.classList.add(locale);
 renderHome(locale);
 document.querySelectorAll('[data-mr][data-en][data-hi]').forEach(el=>el.textContent=el.dataset[locale]);
 document.querySelectorAll('[data-setlang]').forEach(el=>el.setAttribute('aria-pressed',String(el.dataset.setlang===locale)));
 document.querySelectorAll('input[name="locale"]').forEach(el=>el.value=locale);
}
document.querySelectorAll('[data-setlang]').forEach(el=>el.addEventListener('click',()=>{
 locale=el.dataset.setlang;
 document.cookie=`shakuntala_locale=${locale};path=/;max-age=31536000;SameSite=Lax`;
 translate(); renderReview();
}));
translate();
const formMessages={en:{invalid:'Please check this field.',valid:'Looks good',sending:'Sending…'},mr:{invalid:'कृपया ही माहिती तपासा.',valid:'माहिती योग्य आहे',sending:'पाठवत आहे…'},hi:{invalid:'कृपया यह विवरण जाँचें।',valid:'विवरण सही है',sending:'भेज रहे हैं…'}};
document.querySelectorAll('.form-shell form').forEach(form=>{
 const fields=[...form.querySelectorAll('.field input,.field select,.field textarea')];
 fields.forEach((input,index)=>{
  const field=input.closest('.field');
  const hint=document.createElement('small');hint.className='field-feedback';hint.id=`field-feedback-${index}`;hint.setAttribute('aria-live','polite');field.append(hint);
  input.setAttribute('aria-describedby',`${input.getAttribute('aria-describedby')||''} ${hint.id}`.trim());
  const update=()=>{const active=!input.disabled&&(input.value.length>0||input.dataset.touched==='true');const valid=input.validity.valid;field.classList.toggle('field-valid',active&&valid&&input.value.length>0);field.classList.toggle('field-invalid',active&&!valid);input.setAttribute('aria-invalid',String(active&&!valid));hint.textContent=active?(valid?(input.value?`✓ ${formMessages[locale].valid}`:''):formMessages[locale].invalid):'';};
  input.addEventListener('focus',()=>field.classList.add('field-focused'));
  input.addEventListener('blur',()=>{field.classList.remove('field-focused');input.dataset.touched='true';update()});
  input.addEventListener('input',update);input.addEventListener('change',update);
  input.addEventListener('invalid',()=>{input.dataset.touched='true';update()});
  document.querySelectorAll('[data-setlang]').forEach(button=>button.addEventListener('click',update));
  if(input.tagName==='TEXTAREA'&&input.maxLength>0){const count=document.createElement('small');count.className='character-count';field.append(count);const recount=()=>count.textContent=`${input.value.length} / ${input.maxLength}`;input.addEventListener('input',recount);recount()}
 });
 form.addEventListener('submit',event=>{if(form.dataset.submitting){event.preventDefault();return}if(!form.checkValidity())return;form.dataset.submitting='true';form.setAttribute('aria-busy','true');const button=event.submitter||form.querySelector('button[type="submit"]');if(button){button.dataset.originalLabel=button.innerHTML;button.disabled=true;button.classList.add('is-sending');button.textContent=formMessages[locale].sending}});
});
window.addEventListener('pageshow',event=>{
 if(!event.persisted)return;
 try{if(wizard&&sessionStorage.getItem('shakuntala-wizard-completed')===draftKey){location.reload();return}}catch{}
 document.querySelectorAll('form[data-submitting]').forEach(form=>{
  delete form.dataset.submitting;form.removeAttribute('aria-busy');
  const button=form.querySelector('button.is-sending');
  if(button){button.disabled=false;button.classList.remove('is-sending');button.innerHTML=button.dataset.originalLabel||button.innerHTML}
 });
});
const toggle=document.getElementById('mobileToggle');
function closeMenu() { document.getElementById('site-nav')?.classList.remove('open');toggle?.setAttribute('aria-expanded','false'); }
toggle?.addEventListener('click',()=>{const nav=document.getElementById('site-nav');nav.classList.toggle('open');toggle.setAttribute('aria-expanded',String(nav.classList.contains('open')))});
document.querySelectorAll('#site-nav a').forEach(link=>link.addEventListener('click',closeMenu));
document.addEventListener('keydown',event=>{if(event.key==='Escape')closeMenu()});

const wizard=document.querySelector('[data-wizard]');
const panes=wizard?[...wizard.querySelectorAll('.wizard-pane')]:[];
let step=0;
const draftKey=`shakuntala-wizard-draft:v1:${location.pathname}`;
const draftFields=wizard?[...wizard.querySelectorAll('.field input,.field select,.field textarea,.consent input')].filter(el=>el.name&&el.type!=='file'):[];
function saveDraft(){
 if(!wizard)return;
 try{if(sessionStorage.getItem('shakuntala-wizard-completed')===draftKey)return}catch{}
 const fields=Object.fromEntries(draftFields.map(el=>[el.name,el.type==='checkbox'?el.checked:el.value]));
 const proof=wizard.querySelector('input[type="file"]')?.files?.[0]?.name||'';
 try{sessionStorage.setItem(draftKey,JSON.stringify({step,fields,proof,expiresAt:Date.now()+86400000}))}catch{}
}
if(document.querySelector('[data-application-saved]')){
 try{sessionStorage.removeItem('shakuntala-wizard-draft:v1:/franchise/application');sessionStorage.setItem('shakuntala-wizard-completed','shakuntala-wizard-draft:v1:/franchise/application')}catch{}
}
if(wizard&&document.querySelector('.notice.success')){try{sessionStorage.removeItem(draftKey)}catch{}}
if(wizard){
 try{
  if(sessionStorage.getItem('shakuntala-wizard-completed')===draftKey)sessionStorage.removeItem('shakuntala-wizard-completed');
  const draft=JSON.parse(sessionStorage.getItem(draftKey)||'null');
  if(draft&&draft.expiresAt>Date.now()){
   if(!document.querySelector('.notice.error'))draftFields.forEach(el=>{if(Object.hasOwn(draft.fields||{},el.name)){if(el.type==='checkbox')el.checked=draft.fields[el.name]===true;else if(typeof draft.fields[el.name]==='string')el.value=draft.fields[el.name]}});
   step=Number.isInteger(draft.step)?Math.max(0,Math.min(draft.step,panes.length-1)):0;
   const note=document.createElement('p');note.className='notice draft-restored';note.setAttribute('role','status');
   const labels={en:'Your application draft has been restored. Continue where you left off.',mr:'तुमचा अर्जाचा मसुदा पुनर्स्थापित झाला. मागील टप्प्यापासून पुढे जा.',hi:'आपका आवेदन मसौदा बहाल हो गया है। जहाँ छोड़ा था वहीं से आगे बढ़ें।'};
   note.textContent=labels[locale];wizard.prepend(note);
   if(draft.proof){const proofNote=document.createElement('p');proofNote.className='notice';proofNote.textContent={en:'Please attach your transaction receipt again before submitting.',mr:'पाठवण्यापूर्वी व्यवहाराची पावती पुन्हा जोडा.',hi:'जमा करने से पहले लेनदेन की रसीद फिर संलग्न करें।'}[locale];wizard.querySelector('[data-payment-fields]')?.append(proofNote)}
  }else sessionStorage.removeItem(draftKey);
 }catch{}
 draftFields.forEach(el=>el.dispatchEvent(new Event('input',{bubbles:true})));
}
document.querySelectorAll('[data-payment-fields]').forEach(section=>{
 const method=section.querySelector('[data-payment-method]'),details=section.querySelector('[data-transaction-details]');
 const sync=()=>{const offline=['bank','upi','qr','razorpay'].includes(method.value);details.hidden=!offline;details.querySelectorAll('input,textarea').forEach(input=>{input.disabled=!offline;input.required=offline&&input.hasAttribute('data-transaction-required')})};
 method.addEventListener('change',sync);sync();
});
function renderReview() {
 const review=document.getElementById('application-review'); if(!review||!wizard)return;
 review.replaceChildren();
 for(const el of wizard.querySelectorAll('.form-grid input, .form-grid select, .form-grid textarea')) {
  if(el.disabled||!el.value)continue;
  if(el.closest('[data-payment-fields]'))continue;
  const dt=document.createElement('dt');dt.textContent=el.closest('label').querySelector('span')?.textContent||el.name;
  const dd=document.createElement('dd');dd.textContent=el.tagName==='SELECT'?el.selectedOptions[0].textContent:el.value;
  review.append(dt,dd);
 }
}
function showStep() {
 panes.forEach((pane,index)=>pane.hidden=index!==step);
 wizard.querySelectorAll('.wizard-track li').forEach((el,index)=>{el.classList.toggle('active',index<=step);if(index===step)el.setAttribute('aria-current','step');else el.removeAttribute('aria-current')});
 wizard.querySelector('[data-back]').hidden=step===0;
 wizard.querySelector('[data-next]').hidden=step===panes.length-1;
 renderReview();
 saveDraft();
}
if(wizard) {
 wizard.querySelector('.wizard-actions').hidden=false;
 const gst=wizard.querySelector('[name="has_gst"]'), number=wizard.querySelector('[name="gst_number"]');
 const syncGst=()=>{number.required=gst.value==='1';number.disabled=gst.value!=='1';number.closest('label').hidden=gst.value!=='1'};
 if(gst&&number){gst.addEventListener('change',syncGst);syncGst()}showStep();
 draftFields.forEach(el=>{el.addEventListener('input',saveDraft);el.addEventListener('change',saveDraft)});
 wizard.addEventListener('change',saveDraft);
 window.addEventListener('pagehide',saveDraft);
 wizard.querySelectorAll('.legal-links a').forEach(link=>link.addEventListener('click',saveDraft));
 wizard.querySelector('[data-next]').addEventListener('click',()=>{
  const invalid=[...panes[step].querySelectorAll('input,select,textarea')].find(el=>!el.checkValidity());
  if(invalid){invalid.reportValidity();return}step++;showStep();panes[step].querySelector('input,select,textarea,button')?.focus({preventScroll:true});wizard.scrollIntoView({block:'start',behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});
 });
 wizard.querySelector('[data-back]').addEventListener('click',()=>{step=Math.max(0,step-1);showStep()});
 wizard.addEventListener('invalid',event=>{const pane=event.target.closest('.wizard-pane');if(pane){step=panes.indexOf(pane);showStep()}},true);
 wizard.addEventListener('keydown',event=>{if(event.key==='Enter'&&event.target.tagName==='INPUT'&&step<panes.length-1){event.preventDefault();wizard.querySelector('[data-next]').click()}});
}
const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible');observer.unobserve(entry.target)}}),{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>observer.observe(el));
const particles=document.getElementById('particles');
if(particles&&!matchMedia('(prefers-reduced-motion: reduce)').matches){for(let i=0;i<22;i++){const particle=document.createElement('span');particle.className='floating';particle.dataset.symbol=['✦','✧','❋','◈'][i%4];particle.style.cssText=`left:${Math.random()*100}%;top:${Math.random()*55}%;font-size:${8+Math.random()*20}px;color:#ffc944;animation-duration:${7+Math.random()*10}s;animation-delay:${-Math.random()*16}s`;particles.append(particle)}}

