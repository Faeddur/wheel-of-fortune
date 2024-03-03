<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рулетка</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            font-family: 'Arial', sans-serif;
            font-size: 20px;
        }

        body {
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100%;
        }
        
        .display {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 50rem;
            background-color: #111;
            width: 100%;
            height: 100%;
        }

        .hello {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        canvas {
            display: block;
            width: 100%;
            max-width: 1000px;
            position: relative;
            z-index: 2;
        }

        .list {
            min-width: 300px;
            max-width: 500px;
            width: 100%;
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        .list-items {
            margin: 1rem 0;
            flex-grow: 1;
            overflow-y: scroll;
            border: 1px solid #999;
        }

        .list-item {
            position: relative;
            width: 100%;
            border: 1px solid #dedede;
            padding: 0.3rem;
            cursor: pointer;
        }

        .list-item:hover {
            background-color: rgba(255, 0, 0, 0.1);
        }

        .list-item::after, 
        .list-item::before {
            opacity: 0;
            content: '';
            position: absolute;
            right: 0.4rem;
            top: 50%;
            width: 1rem;
            height: 2px;
            background-color: red;
            transform: translateY(-1px) rotate(45deg);
        }

        .list-item:hover::before,
        .list-item:hover::after {
            opacity: 1;
        }

        .list-item::before {
            transform: translateY(-1px) rotate(-45deg);
        }

        .list-add {
            display: flex;
            gap: 1rem;
        }

        .list-add input {
            flex-grow: 1;
        }

        .wheel-type label {
            display: block;
            margin-bottom: 1rem;
            cursor: pointer;
        }

        button, input {
            cursor: pointer;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            min-width: 2rem;
        }

        button {
            min-width: max-content;
        }

        #spin {
            width: 100%;
        }

        #custom-spin {
            width: 100%;
            margin-top: 1rem;
        }

        .popup {
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            padding: 2rem;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .popup.opened {
            display: flex;
        }

        .popup__wrapper {
            position: relative;
            padding: 3rem;
            width: 100%;
            max-width: 30rem;
            background-color: #ffffff;
            text-align: center;
        }

        .popup__close {
            position: absolute;
            right: 0.5rem;
            top: 0.5rem;
            width: 1.5rem;
            height: 1.5rem;
            pointer-events: all;
            cursor: pointer;
        }

        .popup__close::after, 
        .popup__close::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 2px;
            background-color: #111111;
            transform: translateY(-1px) rotate(45deg);
        }

        .popup__close::before {
            transform: translateY(-1px) rotate(-45deg);
        }

        .popup__text {
            font-size: 1.3rem;
        }

        .hello {
            color: #ffffff;
        }

        .claims {
            font-size: 0.8rem;
            color: #ffffff;
            position: fixed;
            left: 1rem;
            bottom: 1rem;
            z-index: 999;
        }

        [data-popup="success"] {
            background-image: url(./fire.gif);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="display">
            <div class="hello">
                <h1>Wheel of Fortune</h1>
                <br>
                <p>Добавь хотя бы 2 элемента чтобы крутануть</p>
            </div>
        </div>
        <div class="list">
            <form class="list-add" id="add-form">
                <input type="text" placeholder="Введите текст" id="add-input">
                <button type="submit" id="add">добавить</button>
            </form>

            <div class="list-items" id="list-items">
                
            </div>

            <div class="wheel-type">
                <label>
                    <input type="radio" name="wheel-type" checked value="common">
                    <span>Обычный режим</span>
                </label>
                
                <label>
                    <input type="radio" name="wheel-type" value="custom">
                    <span>На вылет</span>
                </label>
                
            </div>
            <button id="spin" disabled>Крутить барабан!</button>
        </div>
    </div>
    
    <div class="popup" data-popup="success">
        <div class="popup__wrapper">
            <div class="popup__close"></div>
            <div class="popup__text" id="inner-text"></div>
        </div>
    </div>

    <div class="popup" data-popup="custom">
        <div class="popup__wrapper">
            <div class="popup__close"></div>
            <div class="popup__text" id="inner-text-custom"></div>
            <button id="custom-spin">Крутить дальше</button>
        </div>
    </div>

    <div class="claims">by Danchess</div>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
<script>

class Wheel {
    /**
     * @type {HTMLCanvasElement}
     */
    canvas;

    /**
     * @type {CanvasRenderingContext2D}
     */
    ctx;

    radius = 400;

    width = 1000;
    height = 1000;

    currentAngle = 0;
    speed = 0;
    coeff = 0.01;
    treshold = 0.03;

    items = [];

    /**
     * @type {HTMLElement}
     */
    spinButton;

    /**
     * @type {HTMLElement}
     */
    addButton;

    /**
     * @type {HTMLElement}
     */
    addForm;

    /**
     * @type {HTMLElement}
     */
    input;

    /**
     * @type {HTMLElement}
     */
    list;

    /**
     * @type {HTMLElement}
     */
    successPopup;

    /**
     * @type {HTMLElement}
     */
    customPopup;

    /**
     * @type {'common'|'custom'}
     */
    mode = 'custom';

    isSpining = false;

    soundSuccess = new Audio('./fanfary.mp3');
    soundFail = new Audio('./neudacha.mp3');

    constructor() {
        this.spinButton = document.querySelector('#spin');
        this.addButton = document.querySelector('#add');
        this.input = document.querySelector('#add-input');
        this.list = document.querySelector('#list-items');
        this.successPopup = document.querySelector('[data-popup="success"]');
        this.customPopup = document.querySelector('[data-popup="custom"]');
        this.addForm = document.querySelector('#add-form');
        this.initCanvas();
        this.loop();
        this.addListeners();
    }

    addListeners() {
        this.spinButton.addEventListener('click', () => {
            if (this.items.length < 2) {
                Toastify({
                    text: "Add at least 2 elements",
                    gravity: "bottom", 
                    position: "center",
                }).showToast();
                return;
            }
            if (this.isSpining) {
                Toastify({
                    text: "Already spinning",
                    gravity: "bottom", 
                    position: "center",
                }).showToast();
                return;
            }

            this.spin();
        });

        this.addForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const value = this.input.value;

            if (this.items.length > 20) {

                Toastify({
                    text: "Max 20 elements",
                    gravity: "bottom", 
                    position: "center",
                }).showToast();

                return;
            }

            if (!value.trim()) {
                Toastify({
                    text: "Empty value",
                    gravity: "bottom", 
                    position: "center",
                }).showToast();
                return;
            }

            if (this.isSpining) {
                Toastify({
                    text: "Wheel is spinning",
                    gravity: "bottom", 
                    position: "center",
                }).showToast();
                return;
            }

            this.addItem(value);
        });

        Array.from(document.querySelectorAll('.popup__close')).forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.popup')?.classList.remove('opened');
            });
        });

        const customBtn = document.querySelector('#custom-spin');
        customBtn.addEventListener('click', () => {
            this.customPopup.classList.remove('opened');
            this.spin();
        });

        const radios = Array.from(document.querySelectorAll('[name="wheel-type"]'));

        radios.forEach(el => {
            el.addEventListener('change', () => {
                this.mode = radios.find(r => r.checked)?.value;
                console.log(this);
            });
        })
    }

    spin() {
        this.speed += this.getRandomForce();
        this.isSpining = true;
    }
    
    addItem(text = '') {
        const html = document.createElement('div');
        const uuid = this.uuidv4();

        html.classList.add('list-item');
        html.innerText = text;
        html.dataset.id = uuid,

        this.list.append(html);

        html.addEventListener('click', () => {
            this.removeItem(uuid);
        });

        this.input.value = '';

        this.items.push({
            angle: 0,
            html: html,
            text: text,
            color: this.getRandomColor(),
            id: uuid,
        });

        this.spinButton.disabled = this.items.length < 2;
    }

    removeItem(id) {
        if (this.isSpining) {
            Toastify({
                text: "Wheel is spinning",
                gravity: "bottom", 
                position: "center",
            }).showToast();
            return;
        }

        const item = this.items.find(el => el.id === id);
        item.html.remove();
        this.items.splice(this.items.indexOf(item), 1);

        this.spinButton.disabled = this.items.length < 2;
    }

    loop() {
        this.speed += (0 - this.speed) * this.coeff;
        this.currentAngle += this.speed * 0.01;

        if (this.isSpining && this.speed < this.treshold) {
            this.speed = 0;
            this.isSpining = false;
            this.findItem();
        }

        this.draw();
        requestAnimationFrame(this.loop.bind(this));
    }

    findItem() {
        const map = this.items.map(el => el.angle);
        const max = Math.max(...map);
        const index = map.indexOf(max);
        this.onFindItem(this.items[index]);
    }

    onFindItem(item) {
        const { text } = item;

        if (this.mode === 'common') {
            this.success(item);
            return;
        }

        if (this.items.length >= 2) {
            if (this.items.length > 2) {
                this.customProcess(item);
            }
        
            this.removeItem(item.id);
        }

        if (this.items.length === 1) {
            this.success(this.items[0]);
            return;
        } 
    }

    success(item) {
        this.soundSuccess.play();
        const textEl = this.successPopup.querySelector('#inner-text');
        textEl.textContent = `Побеждает «${item.text}» !!!`;
        this.successPopup.classList.add('opened');
    }

    customProcess(item) {
        this.soundFail.play();

        const messages = [
            'Увы и ах, выбывает *',
            'К несчастью (или нет) выбывает *',
            'О, горе, вылетает *',
            'Блииин, уходит *',
            'Утрата невосполнимая. *.',
            'Скорбим по *.',
            '* - cюда...',
        ];

        const random = Math.floor(Math.random() * messages.length);
        const template = messages[random];
        const mes = template.replace('*', `«${item.text}»`);

        const textEl = this.customPopup.querySelector('#inner-text-custom');
        textEl.textContent = mes;

        this.customPopup.classList.add('opened');
    }

    initCanvas() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        document.querySelector('.display').append(this.canvas);
    }

    draw() {
        this.ctx.clearRect(0, 0, this.width, this.height);
        if (this.items.length < 2) {
            this.canvas.style.opacity = 0;
        } else {
            this.canvas.style.opacity = 1;
            this.drawCircle();
            this.drawParts();
            this.drawElements();
        }
    }

    drawCircle() {
        this.ctx.fillStyle = "#111111";
        this.ctx.fillRect(0, 0, this.width, this.height);

        this.ctx.fillStyle = '#ffffff';
        this.ctx.beginPath();
        this.ctx.arc(this.width / 2, this.height / 2, this.radius, 0, Math.PI * 2);
        this.ctx.fill();
    }

    drawParts() {
        const angle = Math.PI * 2 / this.items.length;
        const [cx, cy] = [this.width / 2, this.height / 2];

        this.items.forEach((item, i) => {
            const startAngle = angle * i + this.currentAngle;
            const endAngle = angle * (i + 1) + this.currentAngle;

            item.angle = (startAngle * 180 / Math.PI) % 360;

            this.ctx.fillStyle = item.color;
            this.ctx.strokeStyle = '#111111';
            this.ctx.beginPath();
            this.ctx.moveTo(cx,cy);
            this.ctx.arc(cx, cy, this.radius, startAngle, endAngle);
            this.ctx.lineTo(cx,cy);
            this.ctx.closePath();
            this.ctx.fill();
            this.ctx.stroke();
            this.ctx.save();
            this.ctx.translate(cx, cy );
            this.ctx.fillStyle = '#111111';
            this.ctx.rotate(startAngle + angle * 0.5);
            this.ctx.font = '20px Arial';
            this.ctx.fillText(item.text, this.radius * 0.2, 7, this.radius * 0.6);
            this.ctx.restore();
        });
    }

    drawElements() {
        this.ctx.fillStyle = '#d65959';
        this.ctx.beginPath();
        this.ctx.arc(this.width / 2, this.height / 2, 20, 0, Math.PI * 2);
        this.ctx.fill();

        
        const [startX, startY] = [
            this.width / 2 + this.radius - 20,
            this.height / 2,
        ];
        this.ctx.beginPath();
        this.ctx.moveTo(startX, startY);
        this.ctx.lineTo(startX + 50, startY - 20);
        this.ctx.lineTo(startX + 50, startY + 20);
        this.ctx.closePath();
        this.ctx.fill();
    }

    getRandomForce() {
        return Math.random() * 40 + Math.random() * 30 + 20
    }

    getRandomColor() {
        const [r, g, b] = [
            Math.random() * 100 + 155,
            Math.random() * 100 + 155,
            Math.random() * 100 + 155,
        ];

        return `rgb(${r}, ${g}, ${b})`;
    }

    uuidv4() {
        return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
        );
    }
};

new Wheel();

</script>
</body>
</html>