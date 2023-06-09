<?php

// Check si l'utilisateur est admin pour accéder à la page admin
if (isset($_SESSION['users']) && $_SESSION['users']['admin']) {

    ob_start();

    $genres = $requeteGenre->fetchAll();
    $realisateurs = $requeteRealisateur->fetchAll();
    $acteurs = $requeteActeur->fetchAll();
    $acteursJSON = json_encode($acteurs); // json_encode() permet de convertir une valeur PHP en une chaîne JSON
    $titres = $requeteTitres->fetchAll();

?>

    <!-- Modal addFilm -->

    <div id="modalAddFilm" class="modal">
        <div>Ajouter un film</div>
        <div class="modal-body modal-scroll">
            <form action="index.php?action=admin" method="POST" enctype="multipart/form-data">
                <!-- Affiche -->
                <label for="affiche">Affiche :</label>
                <input type="file" id="affiche" name="affiche" accept="image/png, image/jpeg">

                <!-- Titre -->
                <label for="titre">Titre :</label>
                <input type="text" id="titre" name="titre" required maxlength="50" size="20" autocomplete="off">

                <!-- Note -->
                <label for="note">Note (1,0 à 5):</label>
                <input type="number" id="note" name="note" min="1.0" max="5.0" step="0.1" autocomplete="off">

                <!-- Date de Sortie -->
                <label for="dateSortie">Date de Sortie :</label>
                <input type="date" id="dateSortie" name="dateSortie" required size="20">

                <!-- Durée -->
                <label for="duree">Durée :</label>
                <input type="number" id="duree" name="duree" required min="1" step="1" autocomplete="off">

                <!-- Genre -->
                <fieldset aria-required="true">
                    <legend>Genre (minimum 1):</legend>
                    <?php
                    foreach ($genres as $genre) { ?>
                        <label for="genre<?= $genre['id_genre']; ?>"><?= $genre['nom']; ?> :</label>
                        <input type="checkbox" id="genre<?= $genre['id_genre']; ?>" name="idGenre[]" value="<?= $genre['id_genre']; ?>"><br>
                    <?php } ?>
                    <div id="genres-container">
                        <h2>Créer un nouveau genre</h2>
                        <button id="ajouter-genre">+</button>
                    </div>
                </fieldset>

                <!-- Synopsis -->
                <label for="synopsis">Synopsis :</label>
                <textarea id="synopsis" name="synopsis"></textarea>

                <!-- Réalisateur -->
                <label for="idRealisateur">Réalisateur :</label>
                <select id="idRealisateur" name="idRealisateur">
                    <option value="">Sélectionner un réalisateur</option>
                    <?php foreach ($realisateurs as $realisateur) { ?>
                        <option value="<?= $realisateur['id_realisateur']; ?>"><?= $realisateur['name']; ?></option>
                    <?php } ?>
                </select>

                <br>
                <!-- Ajouter un réalisateur -->
                <legend>Ajouter un réalisateur</legend>
                <!-- Nom -->
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" maxlength="50" size="20" autocomplete="off">
                <!-- Prenom -->
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" maxlength="50" size="20" autocomplete="off">
                <!-- Sexe -->
                <select name="sexe">
                    <option name="sexe" value="H">Homme</option>
                    <option name="sexe" value="F">Femme</option>
                </select>
                <!-- Date de naissance -->
                <label for="dateNaissance">Date de naissance :</label>
                <input type="date" id="dateNaissance" name="dateNaissance" size="20">

                <!-- Bouton ajouter le film -->
                <div class="button">
                    <input type="submit" name="filmSubmit" id="submit" Value="Ajouter le film">
                </div>
            </form>

        </div>
        <!-- Bouton ferme le modal addFilm -->
        <button onclick="closeModal('#modalAddFilm')" class="modal-button">Fermer</button>
    </div>

    <!-- Modal addCasting -->

    <div id="modalAddCasting" class="modal">
        <div>Ajouter un casting/des castings</div>
        <div class="modal-body modal-scroll">

            <form action="index.php?action=admin" method="POST" enctype="multipart/form-data">
                <p>Ajout d'un casting pour le film :
                    <?
                    if (!empty($_SESSION['titre'])) {
                        echo $_SESSION['titre'];
                    } ?>
                </p>

                <!-- Selection acteur(s) existant -->
                <div id="acteurs-select-container">
                    <legend>Ajouter un/des acteur(s) existant dans la base de données</legend>
                    <button id="ajouter-select-acteur">+</button>
                </div>



                <div id="acteurs-input-container">
                    <!-- Ajouter acteur(s) -->
                    <legend>Ajouter un ou plusieurs acteur(s)</legend>
                    <button id="ajouter-acteur">+</button>
                </div>

                <!-- Bouton ajouter le film -->
                <div class="button">
                    <input type="submit" name="castingSubmit" id="submit" Value="Ajouter le casting au film">
                </div>
            </form>

        </div>
        <!-- Bouton ferme le modal addFilm -->
        <button onclick="closeModal('#modalAddCasting')" class="modal-button">Fermer</button>
    </div>

    <section id="admin">
        <div class="header adminHeader">
            <div class="lettres">
                <?php
                foreach (range('A', 'Z') as $lettre) { ?>
                    <a type="button" class="lettre" href="#<?= $lettre; ?>"><?= $lettre; ?></a>
                <?php } ?>
            </div>
        </div>

        <div class="main">
            <div class="liste">
                <!-- Lettre position verticale avec titre de film trié par ordre alphabétique -->
                <?php
                foreach (range('A', 'Z') as $lettre) { ?>
                    <div class="liste-lettre-film">
                        <div class="lettre" id="<?= $lettre; ?>"><?= $lettre; ?></div>
                        <div class="film">
                            <?php
                            foreach ($titres as $titre) {
                                $search  = array('À', 'É', 'Ê', "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", " "); // voir REGEX
                                $replace = array('A', 'E', 'E', "", "", "", "", "", "", "", "", "", "", "");
                                $film = str_replace($search, $replace, $titre['titre']);
                                $filmMaj = ucfirst($film);

                                if (substr($filmMaj, 0, 1) === $lettre) { ?>
                                    <div class="titreFilm" onclick="afficherMenuDeroulant('#menuId<?= $titre['id_film']; ?>')"><?= $titre['titre']; ?></div>
                                    <div id="menuId<?= $titre['id_film']; ?>" class="menuFilm" style="display: none;">
                                        <a href="index.php?action=modifierFilm&id=<?= $titre['id_film']; ?>">Modifier</a>
                                        <form action="index.php?action=admin&id=<?= $titre['id_film']; ?>" method="POST" enctype="multipart/form-data">
                                            <div class="buttonHidden">
                                                <input type="submit" name="supprimerFilmSubmit" id="supprimerFilmSubmit<?= $titre['id_film']; ?>" value="Supprimer">
                                            </div>
                                            <div class="button">
                                                <button onclick="openModalConfirmationSupprimerFilm(event, 'supprimerFilmSubmit<?= $titre['id_film']; ?>', '<?= addslashes($titre['titre']) ?>')" id="supprimerFilmButton">Supprimer</button>
                                                <!-- Retourne la chaîne str après avoir échappé tous les caractères qui doivent l'être. Ces caractères sont : ' " \ NUL -->
                                            </div>
                                        </form>
                                    </div>
                            <?php
                                }
                            } ?>
                        </div>
                    </div>
                <?php } ?>

                <!-- Modal : Bouton Ajouter un film -->

                <button onclick="openModal('#modalAddFilm')" class="addFilm">Ajouter un film</button>

            </div>
        </div>
    </section>

    <script>
        var acteursData = <?php echo $acteursJSON; ?>;
    </script>

<?php

    // redirection si l'utilisateur connecté n'est pas admin
} else {
    $_SESSION['messageError'] = "Vous n'avez pas les droits d'accès à cette page";
    header("Location: index.php?action=accueil");
    exit;
}

$titre = "Admin";
$titre_secondaire = "Admin";
$contenu = ob_get_clean();
require "view/template.php";
