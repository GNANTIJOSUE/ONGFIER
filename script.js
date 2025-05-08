// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
            // Gestion du menu mobile
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }

            // Fermer le menu lors du clic sur un lien
            const links = document.querySelectorAll('.nav-links a');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                    }
                });
            });

            // Animation des cartes de fonctionnalités
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Animation au défilement
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observer les éléments à animer
            document.querySelectorAll('.card, .section-title, .form-container').forEach(el => {
                observer.observe(el);
            });

            // Validation du formulaire côté client
            const membershipForm = document.querySelector('form');
            if (membershipForm) {
                membershipForm.addEventListener('submit', function(e) {
                            let isValid = true;
                            const errors = [];

                            // Validation du nom
                            const nom = document.getElementById('nom');
                            if (!nom.value.trim()) {
                                errors.push('Le nom est requis');
                                isValid = false;
                            }

                            // Validation du prénom
                            const prenom = document.getElementById('prenom');
                            if (!prenom.value.trim()) {
                                errors.push('Le prénom est requis');
                                isValid = false;
                            }

                            // Validation de l'email
                            const email = document.getElementById('email');
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email.value)) {
                                errors.push('Veuillez entrer une adresse email valide');
                                isValid = false;
                            }

                            // Validation du téléphone
                            const telephone = document.getElementById('telephone');
                            const phoneRegex = /^[0-9+\s-]{10,}$/;
                            if (!phoneRegex.test(telephone.value)) {
                                errors.push('Veuillez entrer un numéro de téléphone valide');
                                isValid = false;
                            }

                            // Validation de l'adresse
                            const adresse = document.getElementById('adresse');
                            if (!adresse.value.trim()) {
                                errors.push('L\'adresse est requise');
                                isValid = false;
                            }

                            // Validation du type de membre
                            const typeMembre = document.getElementById('type_membre');
                            if (!typeMembre.value) {
                                errors.push('Veuillez sélectionner un type de membre');
                                isValid = false;
                            }

                            // Afficher les erreurs si nécessaire
                            if (!isValid) {
                                e.preventDefault();
                                const errorContainer = document.querySelector('.error-message');
                                if (errorContainer) {
                                    errorContainer.innerHTML = `
                        <ul>
                            ${errors.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    `;
                    errorContainer.style.display = 'block';
                }
            }
        });
    }

    // Animation on scroll
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const checkScroll = () => {
        animateElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                element.classList.add('scrolled');
            }
        });
    };

    // Check scroll position on load and scroll
    window.addEventListener('load', checkScroll);
    window.addEventListener('scroll', checkScroll);

    // Gestion du formulaire de contact
    const contactForm = document.querySelector('#contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Récupérer les données du formulaire
            const formData = new FormData(this);
            
            // Afficher un message de chargement
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Envoi en cours...';
            submitBtn.disabled = true;

            // Simuler l'envoi du formulaire (à remplacer par votre logique d'envoi réelle)
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                // Afficher un message de succès
                const successMessage = document.createElement('div');
                successMessage.className = 'success-message';
                successMessage.textContent = 'Votre message a été envoyé avec succès !';
                contactForm.appendChild(successMessage);

                // Réinitialiser le formulaire
                contactForm.reset();

                // Supprimer le message après 3 secondes
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            }, 1500);
        });
    }

    // Gestion du formulaire d'inscription
    const joinForm = document.querySelector('.join-form');
    if (joinForm) {
        const submitBtn = joinForm.querySelector('.submit-btn');

        joinForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Désactiver le bouton et afficher le loader
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // Récupérer les données du formulaire
            const formData = new FormData(joinForm);
            
            try {
                const response = await fetch('process_form.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Réinitialiser les messages d'erreur précédents
                document.querySelectorAll('.error-message').forEach(el => el.remove());
                
                if (result.success) {
                    // Afficher le message de succès
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message';
                    successMessage.textContent = result.message;
                    joinForm.prepend(successMessage);
                    
                    // Réinitialiser le formulaire
                    joinForm.reset();
                    
                    // Rediriger vers la page de succès après 2 secondes
                    setTimeout(() => {
                        window.location.href = 'success.html';
                    }, 2000);
                } else {
                    // Afficher les erreurs
                    if (result.errors) {
                        result.errors.forEach(error => {
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'error-message';
                            errorDiv.textContent = error;
                            joinForm.prepend(errorDiv);
                        });
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = 'Une erreur est survenue lors de l\'envoi du formulaire.';
                joinForm.prepend(errorDiv);
            } finally {
                // Réactiver le bouton et retirer le loader
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });
    }
});