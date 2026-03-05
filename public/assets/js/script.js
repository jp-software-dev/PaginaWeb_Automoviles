let carsData = [];
const NUMERO_DIRECTO = "+525539735554";
const EMAIL_CONTACTO = "contacto@globalcarmetepec.mx";
const carsGrid = document.getElementById('cars-grid');
const brandFilter = document.getElementById('brand-filter');
const priceFilter = document.getElementById('price-filter');
const sortFilter = document.getElementById('sort-filter');
const modal = document.getElementById('car-modal');
const closeModalBtns = document.querySelectorAll('.close-modal');
const lightbox = document.getElementById('fullscreen-lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const btnZoomToggle = document.getElementById('btn-zoom-toggle');
const closeLightbox = document.querySelector('.close-lightbox');

let currentModalImages = [];
let currentImageIndex = 0;
let currentCarData = null;
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('DOMContentLoaded', () => {
    // Cargar catálogo si estamos en la página de catálogo
    if (carsGrid) {
        fetch('api/vehicles.php')
            .then(response => response.json())
            .then(data => {
                carsData = data;
                renderCars(carsData);
                if (brandFilter) brandFilter.addEventListener('change', filterCars);
                if (priceFilter) priceFilter.addEventListener('change', filterCars);
                if (sortFilter) sortFilter.addEventListener('change', filterCars);
            })
            .catch(error => console.error('Error al cargar vehículos:', error));
    }

    // Cargar opciones para el select de contacto
    const selectInteres = document.getElementById('car-interest');
    if (selectInteres) {
        fetch('api/vehicles_select.php')
            .then(response => response.json())
            .then(options => {
                selectInteres.innerHTML = '<option value="">Selecciona un vehículo de interés</option>';
                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    selectInteres.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar opciones:', error));
    }

    setupSmoothScroll();
    setupContactForm();
    setupNewsletter();
    setupLightboxEvents();
    setupMobileMenu();
    setupSwipe();
});

// Renderiza las tarjetas de autos
function renderCars(cars) {
    if (!carsGrid) return;
    carsGrid.innerHTML = '';
    if (cars.length === 0) {
        carsGrid.innerHTML = `
            <div style="text-align:center; color:white; grid-column:1/-1; padding: 60px;">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px; color: var(--gold);"></i>
                <h3 style="margin-bottom: 10px;">No se encontraron vehículos</h3>
                <p style="color: var(--gray-light);">Intenta con otros filtros.</p>
            </div>
        `;
        return;
    }
    const fragment = document.createDocumentFragment();
    cars.forEach((car, index) => {
        const card = createCarCard(car);
        card.style.animationDelay = `${index * 0.05}s`;
        fragment.appendChild(card);
    });
    carsGrid.appendChild(fragment);
}

// Crea una tarjeta individual
function createCarCard(car) {
    const card = document.createElement('div');
    card.className = 'car-card';
    const isNew = car.year >= 2024;
    // Ruta corregida a public/assets/images/
    const imagePath = car.imageBase ? `public/assets/images/${car.imageBase}1${car.imageExtension}` : 'public/assets/images/placeholder.jpg';
    card.innerHTML = `
        ${isNew ? '<div class="car-badge">NUEVO</div>' : ''}
        <div class="car-image-container">
            <img src="${imagePath}" alt="${car.brand} ${car.model}" loading="lazy" decoding="async">
        </div>
        <div class="car-details">
            <span class="car-brand">${car.brand}</span>
            <h3>${car.model}</h3>
            <div class="car-price">$${car.price} ${car.priceUnit}</div>
            <div class="car-specs-preview">
                <span><i class="fas fa-calendar-alt"></i> ${car.year}</span>
                <span><i class="fas fa-tachometer-alt"></i> ${car.kilometers} km</span>
                <span><i class="fas fa-palette"></i> ${car.exteriorColor}</span>
            </div>
            <button class="view-details-btn" onclick="openModal(${car.id})">
                <i class="fas fa-eye"></i> Ver Detalles
            </button>
        </div>
    `;
    return card;
}

// Filtra y ordena
function filterCars() {
    if (!carsData.length) return;
    const brandValue = brandFilter.value;
    const priceValue = priceFilter.value;
    const sortValue = sortFilter.value;

    let filtered = carsData.filter(car => {
        const matchBrand = brandValue === 'all' || car.brand === brandValue;
        let matchPrice = true;
        const price = parseFloat(car.price);
        if (priceValue === 'low') matchPrice = price < 5;
        else if (priceValue === 'mid') matchPrice = price >= 5 && price <= 10;
        else if (priceValue === 'high') matchPrice = price > 10;
        return matchBrand && matchPrice;
    });

    if (sortValue !== 'default') {
        filtered.sort((a, b) => {
            const priceA = parseFloat(a.price);
            const priceB = parseFloat(b.price);
            switch(sortValue) {
                case 'price-asc': return priceA - priceB;
                case 'price-desc': return priceB - priceA;
                case 'year-desc': return b.year - a.year;
                default: return 0;
            }
        });
    }
    renderCars(filtered);
}

// Abre el modal de detalles
window.openModal = function(id) {
    if (!modal) return;
    const car = carsData.find(c => c.id === id);
    if (!car) return;
    currentCarData = car;
    currentModalImages = [];
    for (let i = 1; i <= car.totalImages; i++) {
        currentModalImages.push(`public/assets/images/${car.imageBase}${i}${car.imageExtension}`);
    }
    if (currentModalImages.length === 0) {
        currentModalImages.push('public/assets/images/placeholder.jpg');
    }
    currentImageIndex = 0;
    updateModalMainImage();
    document.getElementById('modal-title').innerText = `${car.brand} ${car.model}`;
    document.getElementById('modal-price').innerText = `$${car.price} ${car.priceUnit}`;
    renderThumbnails();
    const specsHTML = `
        <div class="spec-item"><span>Año</span><strong>${car.year}</strong></div>
        <div class="spec-item"><span>Kilómetros</span><strong>${car.kilometers} km</strong></div>
        <div class="spec-item"><span>Exterior</span><strong>${car.exteriorColor}</strong></div>
        <div class="spec-item"><span>Interior</span><strong>${car.interiorColor}</strong></div>
        <div class="spec-item"><span>Motor</span><strong>${car.specs.motor}</strong></div>
        <div class="spec-item"><span>Potencia</span><strong>${car.specs.potencia}</strong></div>
        <div class="spec-item"><span>Aceleración</span><strong>${car.specs.aceleracion}</strong></div>
        <div class="spec-item"><span>Velocidad Máx</span><strong>${car.specs.velocidadMax}</strong></div>
    `;
    document.getElementById('modal-specs').innerHTML = specsHTML;
    document.getElementById('modal-features').innerHTML = car.features.map(f => `<li>${f}</li>`).join('');
    const whatsappBtn = document.getElementById('modal-whatsapp');
    const message = `Hola, me interesa el ${car.brand} ${car.model} (${car.year}) que vi en su inventario de Global Car Metepec.`;
    if(whatsappBtn) {
        whatsappBtn.href = `https://wa.me/${NUMERO_DIRECTO}?text=${encodeURIComponent(message)}`;
    }
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function updateModalMainImage() {
    const mainImg = document.getElementById('modal-main-img');
    mainImg.style.opacity = '0.8';
    mainImg.src = currentModalImages[currentImageIndex];
    setTimeout(() => {
        mainImg.style.opacity = '1';
    }, 150);
    const thumbs = document.querySelectorAll('.gallery-thumbnails img');
    thumbs.forEach((img, idx) => {
        if(idx === currentImageIndex) img.classList.add('active');
        else img.classList.remove('active');
    });
}

function renderThumbnails() {
    const container = document.getElementById('modal-thumbnails');
    container.innerHTML = currentModalImages.map((img, index) => `
        <img src="${img}" 
             onclick="setModalImage(${index})" 
             class="${index === 0 ? 'active' : ''}" 
             alt="Miniatura" loading="lazy">
    `).join('');
}

window.nextImage = function(e) {
    if(e) e.stopPropagation();
    currentImageIndex++;
    if (currentImageIndex >= currentModalImages.length) currentImageIndex = 0;
    updateModalMainImage();
}

window.prevImage = function(e) {
    if(e) e.stopPropagation();
    currentImageIndex--;
    if (currentImageIndex < 0) currentImageIndex = currentModalImages.length - 1;
    updateModalMainImage();
}

window.setModalImage = function(index) {
    currentImageIndex = index;
    updateModalMainImage();
}

// Configura el formulario de contacto
function setupContactForm() {
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
    }
    closeModalBtns.forEach(btn => {
        btn.onclick = function() {
            this.closest('.modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        };
    });
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
}

// Maneja el envío del formulario con validaciones
function handleContactSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const nombre = form.querySelector('input[placeholder="Nombre Completo"]').value;
    const email = form.querySelector('input[type="email"]').value;
    const telefono = form.querySelector('input[type="tel"]').value;
    const selectInteres = document.getElementById('car-interest');
    const autoInteres = selectInteres.options[selectInteres.selectedIndex]?.text || 'No especificado';
    const mensajeUsuario = form.querySelector('textarea').value;

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification("Correo electrónico no válido", "error");
        return;
    }

    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(telefono)) {
        showNotification("El teléfono debe tener 10 dígitos numéricos", "error");
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;

    const textoWhatsapp = `*Hola Global Car Metepec* 🚘%0A%0A👤 *Nombre:* ${nombre}%0A📧 *Correo:* ${email}%0A📱 *Tel:* ${telefono}%0A🚗 *Interés:* ${autoInteres}%0A💬 *Mensaje:* ${mensajeUsuario}`;

    setTimeout(() => {
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        const baseUrl = isMobile ? "https://api.whatsapp.com/send" : "https://web.whatsapp.com/send";
        window.open(`${baseUrl}?phone=${NUMERO_DIRECTO}&text=${textoWhatsapp}`, '_blank');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        form.reset();
        showNotification("¡Abriendo WhatsApp!", "success");
    }, 800);
}

// Funciones de lightbox
function setupLightboxEvents() {
    const mainImg = document.getElementById('modal-main-img');
    if (mainImg) {
        mainImg.addEventListener('click', () => {
            openLightbox(mainImg.src);
        });
    }
    if (closeLightbox) {
        closeLightbox.addEventListener('click', closeLightboxFunc);
    }
    if (btnZoomToggle) {
        btnZoomToggle.addEventListener('click', toggleLightboxZoom);
    }
    if (lightboxImg) {
        lightboxImg.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleLightboxZoom(e);
        });
        lightboxImg.addEventListener('mousemove', (e) => {
            requestAnimationFrame(() => handleZoomMove(e));
        });
        lightboxImg.addEventListener('touchmove', (e) => {
            if (lightboxImg.classList.contains('zoomed')) {
                e.preventDefault();
                requestAnimationFrame(() => handleZoomMove(e));
            }
        }, { passive: false });
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightboxFunc();
            }
        });
    }

    const lightboxPrev = document.getElementById('lightbox-prev-btn');
    const lightboxNext = document.getElementById('lightbox-next-btn');

    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', (e) => {
            e.stopPropagation();
            currentImageIndex--;
            if (currentImageIndex < 0) currentImageIndex = currentModalImages.length - 1;
            lightboxImg.src = currentModalImages[currentImageIndex];
            updateModalMainImage();
        });
    }
    if (lightboxNext) {
        lightboxNext.addEventListener('click', (e) => {
            e.stopPropagation();
            currentImageIndex++;
            if (currentImageIndex >= currentModalImages.length) currentImageIndex = 0;
            lightboxImg.src = currentModalImages[currentImageIndex];
            updateModalMainImage();
        });
    }
}

function openLightbox(src) {
    if (!lightbox) return;
    lightboxImg.src = src;
    lightbox.style.display = 'flex';
    requestAnimationFrame(() => lightbox.classList.add('active'));
    document.body.style.overflow = 'hidden';
}

function closeLightboxFunc() {
    lightbox.classList.remove('active');
    setTimeout(() => {
        lightbox.style.display = 'none';
        lightboxImg.classList.remove('zoomed');
        lightboxImg.style.transformOrigin = 'center center';
        if (btnZoomToggle) btnZoomToggle.classList.remove('active');
    }, 300);
}

function toggleLightboxZoom(e) {
    e.stopPropagation();
    lightboxImg.classList.toggle('zoomed');
    if (btnZoomToggle) btnZoomToggle.classList.toggle('active');
    if (!lightboxImg.classList.contains('zoomed')) {
        lightboxImg.style.transformOrigin = 'center center';
    }
}

function handleZoomMove(e) {
    if (!lightboxImg.classList.contains('zoomed')) return;
    let clientX, clientY;
    if (e.type === 'touchmove' || e.type === 'touchstart') {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    } else {
        clientX = e.clientX;
        clientY = e.clientY;
    }
    const rect = lightboxImg.getBoundingClientRect();
    const x = clientX - rect.left;
    const y = clientY - rect.top;
    const xPercent = (x / rect.width) * 100;
    const yPercent = (y / rect.height) * 100;
    lightboxImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
}

function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(href);
                if (targetElement) targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function setupNewsletter() {
    const newsletterBtn = document.querySelector('.newsletter-input button');
    if (newsletterBtn) {
        newsletterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('input');
            if (input.value.includes('@')) {
                showNotification("¡Gracias por suscribirte!", "success");
                input.value = '';
            } else {
                showNotification("Ingresa un correo válido", "error");
            }
        });
    }
}

function setupMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = hamburger.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.replace('fa-bars', 'fa-times');
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.replace('fa-times', 'fa-bars');
                document.body.style.overflow = 'auto';
            }
        });
    }
}

function setupSwipe() {
    const gallery = document.querySelector('.main-image-wrapper');
    if (!gallery) return;
    gallery.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, {passive: true});
    gallery.addEventListener('touchend', e => { 
        touchEndX = e.changedTouches[0].screenX;
        if (touchEndX < touchStartX - 50) nextImage();
        if (touchEndX > touchStartX + 50) prevImage();
    }, {passive: true});
}

function showNotification(message, type = "success") {
    const notification = document.createElement('div');
    notification.style.cssText = `position: fixed; top: 90px; right: 20px; background: ${type === 'success' ? '#4CAF50' : '#f44336'}; color: white; padding: 15px 25px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3); font-family: sans-serif;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check' : 'fa-exclamation'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

document.addEventListener('contextmenu', e => {
    if (e.target.tagName === 'IMG') e.preventDefault();
});