document.addEventListener('DOMContentLoaded', () => {

    /* 1. MODO OSCURO / CLARO CON PERSISTENCIA EN LOCALSTORAGE */
    const themeBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');

    // Recuperar el tema guardado previamente en el navegador
    const savedTheme = localStorage.getItem('selected-theme');

    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        if (themeIcon) themeIcon.textContent = '🌙';
        if (themeText) themeText.textContent = 'Modo Oscuro';
    } else if (savedTheme === 'light') {
        document.body.removeAttribute('data-theme');
        if (themeIcon) themeIcon.textContent = '☀️';
        if (themeText) themeText.textContent = 'Modo Claro';
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            
            if (isDark) {
                document.body.removeAttribute('data-theme');
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Modo Claro';
                localStorage.setItem('selected-theme', 'light'); // Guardar preferencia clara
            } else {
                document.body.setAttribute('data-theme', 'dark');
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Modo Oscuro';
                localStorage.setItem('selected-theme', 'dark'); // Guardar preferencia oscura
            }
        });
    }

    /* 2. SELECCIÓN DE GIFS ALEATORIOS (CARGA AL INICIO) */
    let currentGif = -1;
    const gifs = document.querySelectorAll('.gif-item');
    const randomGifBtn = document.getElementById('randomGifBtn');

    function showRandomGif() {
        if (gifs.length === 0) return;

        if (currentGif !== -1) {
            gifs[currentGif].classList.remove('active');
        }

        let newIndex;
        do {
            newIndex = Math.floor(Math.random() * gifs.length);
        } while (newIndex === currentGif && gifs.length > 1);

        currentGif = newIndex;
        gifs[currentGif].classList.add('active');
    }

    if (randomGifBtn) {
        randomGifBtn.addEventListener('click', showRandomGif);
    }

    showRandomGif();
});

/* 3. CAMBIO DE TEMA DE ESTUDIO Y DESPLAZAMIENTO AUTO AL BOTÓN SELECCIONADO */
function selectTopic(topicId) {
    const allTopics = document.querySelectorAll('.topic-content');
    allTopics.forEach(topic => topic.classList.remove('active'));

    const allButtons = document.querySelectorAll('.topic-btn');
    allButtons.forEach(btn => btn.classList.remove('active'));

    const targetTopic = document.getElementById(`topic-${topicId}`);
    if (targetTopic) {
        targetTopic.classList.add('active');
    }

    const activeBtn = document.querySelector(`.topic-btn[data-topic="${topicId}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        
        // Auto-scroll del menú lateral para asegurar visibilidad del tema activo
        activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        const locationText = activeBtn.querySelector('.topic-title').innerText;
        const topicNum = activeBtn.querySelector('.topic-num').innerText;
        document.getElementById('current-location').innerText = `${topicNum}. ${locationText}`;
    }

    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 900) {
        sidebar.classList.remove('open');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* 4. MENÚ EN MÓVILES */
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

/* 5. COPIADO DE CÓDIGO */
function copyCode(button) {
    const codeBlock = button.parentElement.nextElementSibling.querySelector('code');
    const textToCopy = codeBlock.innerText;

    navigator.clipboard.writeText(textToCopy).then(() => {
        const originalText = button.innerText;
        button.innerText = "¡Copiado!";
        button.style.backgroundColor = "#16a34a";

        setTimeout(() => {
            button.innerText = originalText;
            button.style.backgroundColor = "#334155";
        }, 2000);
    }).catch(err => {
        console.error("Error al copiar código: ", err);
    });
}