(function(){
  const header = document.getElementById('dynamicHeader');
  const handle = document.getElementById('dhHandle');
  const toggleCorner = document.getElementById('toggleCorner');
  let dragging = false;
  let startX=0, startY=0, origX=0, origY=0;

  // Load saved state (dock edge & offset)
  const STATE_KEY = 'dynamicHeaderState_v1';
  function saveState(state){ localStorage.setItem(STATE_KEY, JSON.stringify(state)); }
  function loadState(){ try{ return JSON.parse(localStorage.getItem(STATE_KEY) || 'null'); }catch(e){return null} }

  function canDrag() {
    return window.innerWidth > 768;
  }

  // Apply dock class and position
  function applyState(state){
    header.classList.remove('dock-top','dock-bottom','dock-left','dock-right');
    header.style.left = '';
    header.style.top = '';
    header.style.right = '';
    header.style.bottom = '';
    header.style.transform = '';

    if(!state) return;
    const edge = state.edge;
    const pos = state.pos ?? 0.5;
    if(edge === 'top'){
      header.classList.add('dock-top');
      header.style.left = (pos*100) + '%';
      header.style.transform = 'translateX(-50%)';
      header.style.top = '12px';
      header.style.bottom = 'auto';
    } else if(edge === 'bottom'){
      header.classList.add('dock-bottom');
      header.style.left = (pos*100) + '%';
      header.style.transform = 'translateX(-50%)';
      header.style.bottom = '12px';
      header.style.top = 'auto';
    } else if(edge === 'left'){
      header.classList.add('dock-left');
      header.style.top = (pos*100) + '%';
      header.style.transform = 'translateY(-50%)';
      header.style.left = '12px';
    } else if(edge === 'right'){
      header.classList.add('dock-right');
      header.style.top = (pos*100) + '%';
      header.style.transform = 'translateY(-50%)';
      header.style.right = '12px';
      header.style.left = 'auto';
    }
  }

  // initial
  const initial = loadState();
  if(initial) applyState(initial);

  // helper to get bounding center normalized
  function getNormalizedCenter(x,y){
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    return {x: x / vw, y: y / vh};
  }

  function onPointerDown(e){
    dragging = true;
    header.classList.add('dragging');
    startX = e.clientX; startY = e.clientY;
    const rect = header.getBoundingClientRect();
    origX = rect.left; origY = rect.top;
    document.addEventListener('pointermove', onPointerMove);
    document.addEventListener('pointerup', onPointerUp);
  }

  function onPointerMove(e){
    if(!dragging) return;
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    header.style.left = (origX + dx) + 'px';
    header.style.top = (origY + dy) + 'px';
    header.style.right = 'auto'; header.style.bottom = 'auto';
    header.style.transform = 'none';
  }

  function onPointerUp(e){
    dragging = false;
    header.classList.remove('dragging');
    document.removeEventListener('pointermove', onPointerMove);
    document.removeEventListener('pointerup', onPointerUp);

    // determine nearest edge by center point
    const rect = header.getBoundingClientRect();
    const cx = rect.left + rect.width/2;
    const cy = rect.top + rect.height/2;

    const {x: nx, y: ny} = getNormalizedCenter(cx, cy);
    // distances to edges (normalized)
    const toTop = ny;
    const toBottom = 1 - ny;
    const toLeft = nx;
    const toRight = 1 - nx;
    const min = Math.min(toTop,toBottom,toLeft,toRight);

    let edge = 'top';
    if(min === toTop) edge = 'top';
    else if(min === toBottom) edge = 'bottom';
    else if(min === toLeft) edge = 'left';
    else if(min === toRight) edge = 'right';

    // compute position along edge (0..1)
    let pos = 0.5;
    if(edge === 'top' || edge === 'bottom'){
      const vw = window.innerWidth;
      pos = Math.min(0.95, Math.max(0.05, (cx / vw)));
    } else {
      const vh = window.innerHeight;
      pos = Math.min(0.95, Math.max(0.05, (cy / vh)));
    }

    const newState = {edge, pos};
    applyState(newState);
    saveState(newState);
  }

  // Attach pointerdown to header handle and header itself
  if (window.innerWidth > 768) {
    handle.addEventListener('pointerdown', onPointerDown);
    header.addEventListener('pointerdown', function (e) {
      // nếu nhấn vào nút bên trong thì không bắt drag
      if (e.target.closest('button') || e.target.closest('a')) return;
      onPointerDown(e);
    });
  }

  // Toggle border-radius example
  if (toggleCorner) {
    toggleCorner.addEventListener('click', ()=>{
      const cur = getComputedStyle(header).borderRadius;
      if(cur.includes('18')){
        header.style.borderRadius = '6px';
      } else {
        header.style.borderRadius = '';
      }
    });
  }

  // keyboard: press D to cycle docks (accessible)
  window.addEventListener('keydown', (e)=>{
    if(e.key.toLowerCase()==='d'){
      const order = ['top','right','bottom','left'];
      const st = loadState() || {edge:'top',pos:0.5};
      const idx = order.indexOf(st.edge || 'top');
      const next = order[(idx+1)%order.length];
      const ns = {edge: next, pos: 0.5};
      applyState(ns); saveState(ns);
    }
  });

  // ensure header stays visible on resize
  window.addEventListener('resize', ()=>{
    const st = loadState();
    if(st) applyState(st);
  });

})();

const toggle = document.getElementById("toggleMenu");
const menu = document.querySelector(".dh__menu");
if (toggle && menu) {
  toggle.addEventListener("click", (e) => {
    e.preventDefault();
    menu.classList.toggle("active");
  });
}

const userToggle = document.getElementById('userToggle');
const userDropdown = document.querySelector('.user-dropdown');

if (userToggle && userDropdown) {
  userToggle.addEventListener('click', (e) => {
    // KIỂM TRA: Nếu chưa đăng nhập thì KHÔNG chặn link
    const isLoggedIn = userToggle.getAttribute('data-loggedin') === 'true';

    if (isLoggedIn) {
      e.preventDefault(); // CHỈ chặn khi đã đăng nhập
      userDropdown.classList.toggle('active');
    }
    // Nếu chưa đăng nhập, link đến login.php sẽ hoạt động bình thường
  });

  // Ẩn menu khi click ra ngoài
  document.addEventListener('click', (e) => {
    if (!userToggle.contains(e.target) && !userDropdown.contains(e.target)) {
      userDropdown.classList.remove('active');
    }
  });
}
