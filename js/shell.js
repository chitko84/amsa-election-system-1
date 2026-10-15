/* Shared, accessible shell interactions. Authentication and vote state stay server-side. */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const page=document.body.dataset.page;
    document.querySelectorAll('.sidebar-link[data-page]').forEach(a=>a.classList.toggle('active',a.dataset.page===page));
    const sidebar=document.querySelector('.sidebar'),overlay=document.querySelector('.sidebar-overlay');
    const toggles=Array.from(document.querySelectorAll('[data-menu-toggle]'));
    let returnFocus=null;
    if(sidebar&&toggles.length){
      if(!sidebar.id)sidebar.id='primary-sidebar';
      sidebar.setAttribute('aria-hidden','true');
      sidebar.inert=true;
      toggles.forEach(btn=>{
        btn.type='button';
        btn.setAttribute('aria-controls',sidebar.id);
        btn.setAttribute('aria-expanded','false');
        if(!btn.getAttribute('aria-label'))btn.setAttribute('aria-label','Open navigation menu');
      });
      if(overlay){overlay.setAttribute('aria-hidden','true');}
      const setOpen=(open,focusMenu=true)=>{
        sidebar.classList.toggle('open',open);
        overlay?.classList.toggle('show',open);
        document.body.classList.toggle('menu-open',open);
        sidebar.inert=!open;
        sidebar.setAttribute('aria-hidden',String(!open));
        if(overlay)overlay.setAttribute('aria-hidden',String(!open));
        toggles.forEach(btn=>{btn.setAttribute('aria-expanded',String(open));btn.setAttribute('aria-label',open?'Close navigation menu':'Open navigation menu');});
        if(open&&focusMenu)(sidebar.querySelector('a,button')||sidebar).focus({preventScroll:true});
        if(!open&&returnFocus){returnFocus.focus({preventScroll:true});returnFocus=null;}
      };
      toggles.forEach(btn=>btn.addEventListener('click',()=>{const opening=!sidebar.classList.contains('open');if(opening)returnFocus=btn;setOpen(opening);}));
      overlay?.addEventListener('click',()=>setOpen(false));
      sidebar.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setOpen(false,false)));
      document.addEventListener('keydown',event=>{if(event.key==='Escape'&&sidebar.classList.contains('open')){event.preventDefault();setOpen(false);}});
    }
    document.querySelectorAll('[data-toggle-pin]').forEach(btn=>btn.addEventListener('click',()=>{
      const input=document.querySelector(btn.dataset.togglePin);
      if(input){input.type=input.type==='password'?'text':'password';btn.setAttribute('aria-pressed',String(input.type==='text'));}
    }));
  });
})();
