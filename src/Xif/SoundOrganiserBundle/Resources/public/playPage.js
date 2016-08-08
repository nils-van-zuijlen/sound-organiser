// src/Xif/SoundOrganiserBundle/Resources/public/playPage.js

///déclaration variables globales
/**
 * Lecteur audio HTML5
 * @type {DOMAudioElement}
 */
var player = document.getElementById('player');

/**
 * contenu du fichier project
 * @type {JSONArray}
 */
var PROJECT;

/**
 * URL du dossier contenant les sons
 * @type {string}
 */
var baseURL;

/**
 * Numéro du son actuel
 * @type {int}
 */
var NOW;

/**
 * Ouverture du projet
 * Modifications d'affichage et ajout d'évenements
 * 
 * @return {void}
 */
function open_proj() {
	///affichage
	//transitions de la liste
	var listElements = document.getElementById('sound_list').getElementsByTagName('li');
	for (var i = listElements.length - 1; i >= 0; i--) {
		var LI = listElements[i];

		LI.
			getElementsByTagName('span')[0].
			innerHTML = 
				'(' +
				songtype_to_str(PROJECT.songs[i]) +
				')';

		(function(i) {
			LI.
				getElementsByTagName('song')[0].
				addEventListener(
					'click',
					function() {
						go_song(i);
					}
					);
		})(i);
	}

	//transition actuelle
	document.
		getElementById('song_trans').
		innerHTML = 
			'(' +
			songtype_to_str(PROJECT.songs[0]) +
			')';

	//transition suivant
	if (typeof PROJECT.songs[1] !== 'undefined')
		document.
			getElementById('next_trans').
			innerHTML = 
				'(' +
				songtype_to_str(PROJECT.songs[1]) +
				')';

	///évenements
	//le son est fini
	player.addEventListener('ended', song_ended);
	//on appuie sur une touche
	document.addEventListener('keydown', key_event);

	//on charge le premier son
	load_song(0);
}

/**
 * Gestion des évenements de type keydown
 * 
 * @param  {Event} e Évenement déclenché
 * @return {bool}    ===false
 */
function key_event(e) {
	switch (e.keyCode) {
		case 32: //appui sur espace: play/pause/stop du son actuel
			//arret de la propagation
			e.stopPropagation();
			//arret de l'évenement normal
			e.preventDefault();
			e.returnValue = false; //pour IE
			//fonction à appeller
			step_song();
			break;

		case 39: //appui sur flèche droite: son suivant
			//arret de la propagation
			e.stopPropagation();
			//arret de l'évenement normal
			e.preventDefault();
			e.returnValue = false; //pour IE
			//fonction à appeller
			next_song();
			break;

		case 37: //appui sur flèche gauche: retour
			e.stopPropagation();
			e.preventDefault();
			e.returnValue = false;

			prev_song();
			break;

		case 86: //appui sur v: baisser le volume
			e.stopPropagation();
			e.preventDefault();
			e.returnValue = false;

			vol_factor(600, 10);
			break;

		case 66: //appui sur b: monter le volume
			e.stopPropagation();
			e.preventDefault();
			e.returnValue = false;

			vol_factor_inv(600, 10);
			break;
	}
}

/**
 * Augmenter le volume
 * 
 * @param  {integer} duration Durée du fondu
 * @param  {integer} interval Intervalle entre deux itérations
 * @return {void}
 */
function vol_factor (duration, interval) {
	var vol = player.volume;
	var goal_vol = vol*PROJECT.vol_factor;
	var volstep = (vol-goal_vol)/(duration/interval);
	var intervalObj = setInterval(function () {
		if (player.volume > goal_vol) {
			player.volume -= volstep;
		} else {
			clearInterval(intervalObj);
		}
	}, interval);
}

/**
 * Augmenter le volume pour lancer
 * 
 * @param  {integer} duration Durée du fondu
 * @param  {integer} interval Intervalle entre deux itérations
 * @return {void}
 */
function fadein (duration, interval) {
	var vol = player.volume;
	var volstep = vol/(duration/interval);
	player.volume = 0;
	player.play();
	var intervalObj = setInterval(function () {
		if (player.volume < vol) {
			player.volume += volstep;
		} else {
			clearInterval(intervalObj);
		}
	}, interval);
}

/**
 * Diminuer le volume
 * 
 * @param  {integer} duration Durée du fondu
 * @param  {integer} interval Intervalle entre deux itérations
 * @return {void}
 */
function vol_factor_inv (duration, interval) {
	var vol = player.volume;
	var goal_vol = vol*1/(PROJECT.vol_factor);
	if (goal_vol > 0.99) goal_vol = 0.99;
	var volstep = (vol-goal_vol)/(duration/interval);
	var intervalObj = setInterval(function () {
		if (player.volume < goal_vol) {
			player.volume -= volstep;
		} else {
			clearInterval(intervalObj);
		}
	}, interval);
}

/**
 * Diminuer le volume pour arréter le son
 * 
 * @param  {integer}  duration Durée du fondu
 * @param  {integer}  interval Intervalle entre deux itérations
 * @param  {function} done     Fonction à exécuter une fois fini
 * @return {void}
 */
function fadeout (duration, interval, done) {
	//récupération du volume actuel
	var vol = player.volume;
	//définition du pas
	var volstep = vol/(duration/interval);
	//arret en cours
	PROJECT.songs[NOW].fadingout = true;
	//définition d'un intervale d'appel
	var intervalObj = setInterval(function () {
		//si le volume est supérieur au pas (le volume n'est pas nul)
		if (player.volume > volstep) {
			//diminuer le volume
			player.volume -= volstep;
		//si le volume est nul
		} else {
			//suppression de l'intervalle
			clearInterval(intervalObj);
			//mise en pause
			player.pause();
			//réinitialisation du volume
			player.volume = vol;
			//pas d'arret en cours
			PROJECT.songs[NOW].fadingout = false;
			//si une fonction a été donnée en argument, l'appeller
			if (done) done();
		}
	}, interval);
}

/**
 * Génere du code HTML à partir d'un son
 * 
 * @param  {array}  SONG Données d'un son
 * @return {string}      Code HTML à insérer
 */
function songtype_to_str(SONG) {
	var str = '';
	//test et ajout des types
		 if (SONG.trans[0] == '1') str += "<songtype one>One</songtype>";
	else if (SONG.trans[0] == '&') str += "<songtype rest>Restart</songtype>";
	else if (SONG.trans[0] == '#') str += "<songtype rand>Random</songtype>";
	else if (SONG.trans[0] == 'O') str += "<songtype rep>Repeat</songtype>";
	else str += "<songtype err>Erreur</songtype>";

	//transition centrale
	if (SONG.trans[1].length) str += '(' + songtrans_to_str(SONG.trans[1]) + ')';

	//transition finale
	str += ',' + songtrans_to_str(SONG.trans[2]);

	return str;
}

/**
 * Génere du code HTML à partir d'un transcode
 * 
 * @param  {string} transcode Transcode dont on veut le code HTML
 * @return {string}           Code HTML généré à insérer
 */
function songtrans_to_str(transcode) {
	var str = '';
	//ajout du HTML de transition
		 if (~transcode.indexOf('q')) str += "<trans quick>Quickfadeout</trans>";
	else if (~transcode.indexOf('s')) str += "<trans sweet>Fadeout</trans>";
	else if (~transcode.indexOf('l')) str += "<trans long>Longfadeout</trans>";
	else if (~transcode.indexOf('f')) str += "<trans full>Full</trans>";
	else if (~transcode.indexOf('r')) str += "<trans raw>Raw</trans>";
	else if (!~transcode.indexOf('n')) str += '<trans err>Erreur</trans>';
	//et de l'Autonext si nécéssaire
	if (~transcode.indexOf('n')) str += "<trans next>Autonext</trans>";

	return str;
}

/**
 * Change le titre de la page en fonction du son en cours
 * 
 * @param  {string} title Titre du projet
 * @param  {string} song  Nom du son en cours
 * @return {void}
 */
function change_title(title, song) {
	var str = '';
	//si un titre est donné au projet
	if (title) {
		//si un titre est donné au son en cours
		if (song) {
			str += song + ' – ';
		}

		str += title;
	}

	//nom du programme
	str += ' – Playing – SoundOrganiser';

	//application des modifications
	document.querySelector('title').innerHTML = str;
}

/**
 * Passe au son suivant en fonction des transitions
 * 
 * @return {void}
 */
function next_song() {
	//si on n'est pas au dernier son
	if (PROJECT.songs.length-1 != NOW) {
		//si on n'est pas en train de jouer un son Full
		if (
			!player.paused 
			&& PROJECT.songs[NOW].trans[2].indexOf('f') == -1
			) {

			//passer au son suivant en transmettant l'autoplay
			go_song(
				NOW+1,
				(~PROJECT.songs[NOW].trans[2].indexOf('n'))
				);
		
		//si on n'est pas en train de jouer un son
		} else if (player.paused) {
			//passer au son suivant sans transmettre d'autoplay
			go_song(NOW+1);
		}
	}
}

/**
 * Passer au son précédent ou retourner au début en fonction des transitions
 * 
 * @param  {boolean} autoplay Définit si il faut lancer le son directement
 * @return {void}
 */
function prev_song(autoplay) {
	//si on est en train de jouer: pause puis retour au début
	if (!player.paused) {
		//pas un Full
		if (!(~PROJECT.songs[NOW].trans[2].indexOf('f'))) {
			if (~PROJECT.songs[NOW].trans[2].indexOf('l')) 
				fadeout(
					4000,
					40,
					function() {
						player.currentTime = 0;
						if (autoplay) player.play()
					}
				);

			else if (~PROJECT.songs[NOW].trans[2].indexOf('s')) 
				fadeout(
					1000,
					20,
					function() {
						player.currentTime = 0;
						if (autoplay) player.play()
					}
				);

			else if (~PROJECT.songs[NOW].trans[2].indexOf('q')) 
				fadeout(
					100,
					10,
					function() {
						player.currentTime = 0;
						if (autoplay) player.play()
					}
				);

			else {
				player.pause();
				player.currentTime = 0;
				if (autoplay) player.play();
			}
		}

	//on n'est pas en train de jouer
	} else {
		//si on est au début et que on n'est pas au premier son
		if (player.currentTime == 0 && NOW != 0) {
			//charger le son précédent
			load_song(NOW-1, autoplay);
		
		} else {
			//remettre au début
			player.currentTime = 0;
			if (autoplay) player.play();
		} 
	}
}

/**
 * Met le son actuel en play/pause/stop en fonction du type de son et des transitions
 * 
 * @return {void}
 */
function step_song() {
	//récupération des métadonnées du son actuel
	var SONG = PROJECT.songs[NOW];
	//si le son est en pause
	if (player.paused) {
		
		//si le son est fini
		if (player.ended) {
			//random: recharger en changeant de fichier et lire
			if (SONG.trans[0] == '#') go_song(NOW, true);
			//repeat: lire
			if (SONG.trans[0] == 'O') player.play();
		
		//si le son n'est pas commencé
		} else if (player.currentTime == 0) {
			//lancer le son
			player.play();

		//sinon, c'est qu'on est en pause au milieu
		} else {
			//restart: relancer à partir de la position actuelle en fonction de la transition
			if (SONG.trans[0] == '&') {
				if      (~SONG.trans[1].indexOf('l')) fadein(4000, 40);
				else if (~SONG.trans[1].indexOf('s')) fadein(1000, 20);
				else if (~SONG.trans[1].indexOf('q')) fadein(100, 10);
				else player.play();//raw
			}

			//repeat: relancer la piste à partir du début
			if (SONG.trans[0] == 'O') {
				//RaZ de la piste
				player.currentTime = 0;
				player.play();
			}
		}
	//le son est en cours de lecture
	} else {
		var transcode = '';
		//si le type est n'est pas One ET que le premier contient qqch d'exploitable on prend le premier transcode
		if (
			!SONG.trans[0] == '1' 
			&& SONG.trans[1].substr(0,1) == '!'
			) {
			
			transcode = SONG.trans[1];
		
		//sinon, on prend le deuxième
		} else {
			transcode = SONG.trans[2];
		}

		var nextsong = false;
		//si le type est One ET que l'autonext est actif
		if (SONG.trans[0] == '1' && ~transcode.indexOf('n')) {
			nextsong = function () {
				load_song(NOW+1, true);
			}
		}
		if (~transcode.indexOf('l'))
			fadeout(4000, 40, nextsong);
		
		if (~transcode.indexOf('s'))
			fadeout(1000, 20, nextsong);
		
		if (~transcode.indexOf('q'))
			fadeout(100, 10, nextsong);
		
		if (~transcode.indexOf('r')) {
			player.pause();
			if (nextsong) nextsong();
		}
	}
}

/**
 * Charger le son indiqué
 * Le lancer si autoplay == true
 * 
 * @param  {integer} NUM      Numéro du son à charger 
 * @param  {boolean} autoplay Lancer le son chargé?
 * @return {void}
 */
function load_song(NUM, autoplay) {
	//le son actuel change
	NOW = NUM;
	//on récupère les métadonnées du son à charger
	var SONG = PROJECT.songs[NUM];

	////DEBUT DES MODIFICATIONS VISIBLES

	//le son actuel n'est pas en cours d'arret
	PROJECT.songs[NUM].fadingout = false;
	//modif du volume du lecteur
	player.volume = SONG.vol;
	//chargement en fonction du type
	if (
		SONG.trans[0] == '1'
		|| SONG.trans[0] == '&'
		|| SONG.trans[0] == 'O'
		) {

		player.src = SONG.file ? baseURL + '/' + SONG.file : player.src;

	} else {
		//la piste à lire est choisie aléatoirement parmi celles disponibles dans le fichier
		player.src = random_src();
	}

	//dans le cas d'un autoplay==true
	if (autoplay) {
		step_song();
	}

	///affichage des métadonnées
	//actuelles
	document.getElementById('song_title').textContent = SONG.name;
	document.getElementById('song_descr').textContent = SONG.descr;
	document.
		getElementById('song_trans').
		innerHTML = songtype_to_str(SONG);

	//suivantes si un suivant existe
	if (PROJECT.songs[NUM+1]) {
		
		document.
			getElementById('next_title').
			textContent = PROJECT.songs[NUM+1].name;
		document.
			getElementById('next_descr').
			textContent = PROJECT.songs[NUM+1].descr;
		document.
			getElementById('next_trans').
			innerHTML = songtype_to_str(PROJECT.songs[NUM+1]);
	
	} else {
		document.getElementById('next_title').textContent = "";
		document.getElementById('next_descr').textContent = "";
		document.getElementById('next_trans').innerHTML = "";
	}

	//titre de la page
	change_title(PROJECT.name, SONG.name);
}

/**
 * Va au son NUM en respectant les transitions du son actuel
 * 
 * @param  {integer} NUM      Son à charger
 * @param  {boolean} autoplay Lancer le son chargé?
 * @return {void}
 */
function go_song(NUM, autoplay) {
	//récupération des méta du son actuel
	var SONG = PROJECT.songs[NOW];
	//si le son actuel est en cours de lecture
	if (!player.paused) {
		//le mettre en pause (avec ou sans fadeout) puis passer au son NUM (de toute façon)
		if (~SONG.trans[2].indexOf('l'))
			fadeout(
				4000,
				40,
				function() {
					load_song(NUM, autoplay);
				}
			);
		else if (~SONG.trans[2].indexOf('s'))
			fadeout(
				1000,
				20,
				function() {
					load_song(NUM, autoplay);
				}
			);
		else if (~SONG.trans[2].indexOf('q'))
			fadeout(
				100,
				10,
				function() {
					load_song(NUM, autoplay);
				}
			);
		else {
			player.pause();
			load_song(NUM, autoplay);
		}
	
	} else {
		load_song(NUM);
	}
}

/**
 * Se lance quand le son en lecture est fini
 * Lance les actions Repeat et Autonext
 * 
 * @return {void}
 */
function song_ended() {
	var SONG = PROJECT.songs[NOW];

	//si le type est Repeat
	if (SONG.trans[0] == 'O') {
		
		//si il y a un autonext et qu'on n'est pas en cours d'arret
		if (
			~SONG.trans[1].indexOf('n') 
			&& !PROJECT.songs[NOW].fadingout
			) {

			player.play();
		}

	} else {
		//si il y a un autonext
		if (~PROJECT.songs[NOW].trans[2].indexOf('n')) {
			load_song(NOW+1, true);
		}
	}
}

/**
 * @return {string} URL d'un son du projet choisi aléatoirement
 */
function random_src() {
	var random = rand(0, PROJECT.songs.length-1, true);
	return baseURL + '/' + PROJECT.songs[random].file;
}

/**
 * Génère un nombre pseudo-aléatoire dans un intervalle donné
 * 
 * @param  {integer}         min     Minimum
 * @param  {integer}         max     Maximum
 * @param  {boolean}         integer Nombre Entier?
 * @return {integer / float}         Nombre généré
 */
function rand(min, max, integer) {
	if (integer) {
		return Math.floor( Math.random() * ( max - min + 1 ) + min );
	} else {
		return Math.random() * (max - min) + min;
	}
}