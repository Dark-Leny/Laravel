{{-- Composant Bouton Favoris --}}
@auth
    @if (auth()->user()->isBibliothecaire())
        {{-- Bouton pour le bibliothécaire (peut ajouter/retirer des favoris) --}}
        <button class="btn btn-outline-secondary favorite-btn" 
                id="favorite-btn-{{ $livre->id }}"
                data-livre-id="{{ $livre->id }}"
                data-is-favorite="{{ $livre->isFavoriteFor(auth()->user()) ? '1' : '0' }}"
                type="button"
                style="transition: all 0.3s ease;">
            <i class="fas fa-heart"></i> 
            <span class="favorite-text">
                {{ $livre->isFavoriteFor(auth()->user()) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
            </span>
        </button>

        <script>
        (function() {
            const btn = document.getElementById('favorite-btn-{{ $livre->id }}');
            if (!btn) {
                console.error('Bouton favoris non trouvé');
                return;
            }

            const livreid = btn.getAttribute('data-livre-id');
            const storageKey = `favorite_livre_${livreid}`;
            
            // Récupérer l'état du localStorage ou du serveur
            let isFavorite = localStorage.getItem(storageKey) !== null 
                ? localStorage.getItem(storageKey) === '1'
                : btn.getAttribute('data-is-favorite') === '1';

            function updateButtonState(shouldSave = true) {
                const textSpan = btn.querySelector('.favorite-text');
                if (isFavorite) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-danger');
                    btn.setAttribute('data-is-favorite', '1');
                    if (textSpan) textSpan.textContent = 'Retirer des favoris';
                    if (shouldSave) localStorage.setItem(storageKey, '1');
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-secondary');
                    btn.setAttribute('data-is-favorite', '0');
                    if (textSpan) textSpan.textContent = 'Ajouter aux favoris';
                    if (shouldSave) localStorage.removeItem(storageKey);
                }
            }

            // Initialiser l'état du bouton au chargement
            updateButtonState(false);

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                btn.disabled = true;
                
                const method = isFavorite ? 'DELETE' : 'POST';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                if (!csrfToken) {
                    console.error('CSRF token non trouvé');
                    btn.disabled = false;
                    return;
                }

                // Changer l'état immédiatement
                isFavorite = !isFavorite;
                updateButtonState(true);

                fetch(`/livres/${livreid}/favorite`, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // État déjà mis à jour, juste persister
                        console.log('Favori mis à jour avec succès');
                    } else {
                        // Revenir à l'état précédent en cas d'erreur
                        isFavorite = !isFavorite;
                        updateButtonState(true);
                        alert('Erreur: ' + (data.message || 'Impossible de modifier le favori'));
                    }
                })
                .catch(error => {
                    // Revenir à l'état précédent en cas d'erreur
                    isFavorite = !isFavorite;
                    updateButtonState(true);
                    console.error('Erreur détaillée:', error);
                    alert('Une erreur est survenue: ' + error.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    // Recharger la page après 2 secondes pour synchroniser avec le serveur
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                });
            });
        })();
        </script>
    @else
        {{-- Pour les non-bibliothécaires: afficher les favoris du bibliothécaire --}}
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Favoris du bibliothécaire:</strong><br>
            @php
                $favorisByBibliothecaires = $livre->favoritedByUsers()
                    ->where('role', '=', 'bibliothécaire')
                    ->count();
            @endphp
            
            @if ($favorisByBibliothecaires > 0)
                Ce livre a été sélectionné comme favori par <strong>{{ $favorisByBibliothecaires }}</strong> 
                bibliothécaire(s) {{ $favorisByBibliothecaires > 1 ? 'de notre équipe' : '' }}.
                <br>
                <small class="text-muted">Seuls les bibliothécaires peuvent gérer les favoris.</small>
            @else
                Ce livre n'a pas encore été sélectionné comme favori par les bibliothécaires.
                <br>
                <small class="text-muted">Seuls les bibliothécaires peuvent gérer les favoris.</small>
            @endif
        </div>
    @endauth
@else
    {{-- Pour les utilisateurs non authentifiés --}}
    <div class="alert alert-warning">
        <i class="fas fa-lock"></i>
        <a href="{{ route('login') }}">Connectez-vous</a> pour voir et gérer les favoris.
    </div>
@endauth
