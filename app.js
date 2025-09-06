const sectionImages = {
  front: './assets/sections/front.jpg',
  top: './assets/sections/top.jpg',
  right: './assets/sections/right.jpg',
  left: './assets/sections/left.jpg',
  back: './assets/sections/back.jpg',
};
let current = 'front';
const state = { front:[], top:[], right:[], left:[], back:[] };
const tabs = document.querySelectorAll('.tab');
const vehicleArea = document.getElementById('vehicleArea');
const sectionImg = document.getElementById('sectionImage');
const issuesList = document.getElementById('issuesList');
const saveBtn = document.getElementById('saveBtn');
const clearBtn = document.getElementById('clearBtn');
const output = document.getElementById('output');
const viewer = document.getElementById('viewer');
const viewerImg = document.getElementById('viewerImg');
const viewerClose = document.getElementById('viewerClose');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    setSection(tab.dataset.panel);
  });
});

function setSection(key){
  current = key;
  sectionImg.src = sectionImages[current];
  render();
}

vehicleArea.addEventListener('click', (e) => {
  const rect = vehicleArea.getBoundingClientRect();
  const isIn = (e.target === vehicleArea || e.target === sectionImg);
  if(!isIn) return;
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;
  const item = { x, y, text:'', image:null };
  state[current].push(item);
  render();
  setTimeout(() => {
    const last = issuesList.querySelector('li:last-child input[type="text"]');
    if(last) last.focus();
  }, 0);
});

function render(){
  // markers
  [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());
  state[current].forEach((it, i) => {
    const m = document.createElement('div');
    m.className = 'marker';
    m.style.left = it.x + '%';
    m.style.top = it.y + '%';
    m.title = (i+1)+'. '+(it.text || 'Detalle');
    vehicleArea.appendChild(m);
  });
  // list
  issuesList.innerHTML='';
  state[current].forEach((it, i) => {
    const li = document.createElement('li'); li.className='issue';
    const num = document.createElement('span'); num.className='num'; num.textContent = (i+1)+'.';
    const input = document.createElement('input'); input.type='text'; input.placeholder='Escribe el detalle'; input.value = it.text;
    input.addEventListener('input', () => { it.text = input.value; });
    const btnImg = document.createElement('button'); btnImg.className='iconbtn'; btnImg.title='Agregar/Cambiar imagen';
    btnImg.innerHTML = '<img src="./assets/icon-img.png" alt="img">';
    btnImg.addEventListener('click', () => pickImageFor(it, i));
    const btnView = document.createElement('button'); btnView.className='iconbtn'; btnView.title='Ver imagen';
    btnView.innerHTML = '<img src="./assets/icon-eye.png" alt="ver">';
    btnView.disabled = !it.image;
    btnView.addEventListener('click', () => { if(!it.image) return; viewerImg.src = it.image; viewer.showModal(); });
    const btnDel = document.createElement('button'); btnDel.className='iconbtn rm'; btnDel.title='Eliminar';
    btnDel.innerHTML = '<img src="./assets/icon-minus.png" alt="del">';
    btnDel.addEventListener('click', () => { state[current].splice(i,1); render(); });
    li.append(num, input, btnImg, btnView, btnDel);
    issuesList.appendChild(li);
  });
}

function pickImageFor(item, index){
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.capture = 'environment';
  input.onchange = (e)=>{
    const file = e.target.files?.[0]; if(!file) return;
    const reader = new FileReader();
    reader.onload = ()=>{ item.image = reader.result; render(); };
    reader.readAsDataURL(file);
  };
  input.click();
}

viewerClose.addEventListener('click', ()=> viewer.close());
saveBtn.addEventListener('click', () => {
  const data = { tecnico: document.getElementById('tecnico').value.trim(), fecha: document.getElementById('fecha').value, secciones: state };
  output.textContent = JSON.stringify(data, null, 2);
});
clearBtn.addEventListener('click', () => { state[current] = []; render(); output.textContent=''; });
setSection('front');