{{-- Composant Bouton Favoris --}}
@auth
    @if (auth()->user()->isBibliothecaire())
        {{-- Bouton pour le bibliothécaire (peut ajouter/retirer des favoris) --}}
        <button id="favorite-btn" 
                class="btn btn-outline-secondary favorite-btn" 
                data-livre-id="{{ $livre->id }}"
                data-is-favorite="{{ $livre->isFavoriteFor(auth()->user()) ? 'true' : 'false' }}">
            <i class="fas fa-heart"></i> 
            <span id="favorite-text">
                {{ $livre->isFavoriteFor(auth()->user()) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
            </span>
        </button>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('favorite-btn');
            const livreid = btn.dataset.livreId;
            let isFavorite = btn.dataset.isFavorite === 'true';

            function updateButtonState() {
                const textSpan = document.getElementById('favorite-text');
                if (isFavorite) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-danger');
                    textSpan.textContent = 'Retirer des favoris';
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-secondary');
                    textSpan.textContent = 'Ajouter aux favoris';
                }
            }

            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                try {
                    const method = isFavorite ? 'DELETE' : 'POST';
                    const response = await fetch(`/livres/${livreid}/favorite`, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        isFavorite = !isFavorite;
                        updateButtonState();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue');
                }
            });
        });
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
