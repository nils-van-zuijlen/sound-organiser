// src/Xif/SoundOrganiserBundle/Resources/public/edit.js

/**
 * @var array PROJECT Projet complet
 * Hydraté via PHP depuis la BDD dans le template HTML
 */
var PROJECT;

/**
 * Numéro du son actuel
 * @type {int}
 */
var NOW;

/**
 * @var {boolean} En train d'éditer
 */
var editing = false;

/**
 * URL du dossier contenant les sons
 * @type {string}
 */
var baseURL;

function load_song_listener(i, LI) {
	LI.getElementsByTagName('song')[0].onclick =
		function() {
			load_song(i);
		};
}

function open_proj() {
	///affichage
	//transitions de la liste
	var listElements = document.getElementById('songList').getElementsByTagName('li');
	for (var i = listElements.length - 1; i >= 0; i--) {
		var LI = listElements[i];

		LI.
			getElementsByTagName('span')[0].
			innerHTML = 
				'(' +
				songtype_to_str(PROJECT.songs[i]) +
				')';

		load_song_listener(i, LI);
	}

	//transition du son affiché
	document.
		getElementById('choosedTrans').
		innerHTML = songtype_to_str(PROJECT.songs[0]);

	///évenements
	document.
		getElementById('projTitle').
		addEventListener('dblclick', function(){edit('projTitle');});
	document.
		getElementById('projDescr').
		addEventListener('dblclick', function(){edit('projDescr');});
	document.
		getElementById('songName').
		addEventListener('dblclick', function(){edit('songName');});
	document.
		getElementById('songDescr').
		addEventListener('dblclick', function(){edit('songDescr');});
	document.
		getElementById('songVol').
		addEventListener('dblclick', function(){edit('songVol');});
	document.
		getElementById('chooseFile').
		addEventListener('click', chooseFile);
	document.
		getElementById('chooseTrans').
		addEventListener('click', chooseTrans);

	//on charge le premier son
	setTimeout(load_song, 5, 0);
}

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

function load_song(NUM) {
	// si on est en cours d'édition, on la stoppe
	if (editing) {
		endEdit();
	}

	NOW = NUM;
	var SONG = PROJECT.songs[NUM];

	document.getElementById('songName').innerHTML = SONG.name;
	if (SONG.descr.length) {
		document.getElementById('songDescr').innerHTML = SONG.descr;
		document.getElementById('songDescr').className = '';
	} else {
		document.getElementById('songDescr').innerHTML = 'Pas de description';
		document.getElementById('songDescr').className = 'no-descr';
	}
	document.getElementById('choosedTrans').innerHTML = songtype_to_str(SONG);
	document.getElementById('songVol').innerHTML = SONG.vol;
	getFileName();
}

function getFileName() {
	if (PROJECT.songs[NOW].file){
		var xhr = new XMLHttpRequest();
		xhr.open('HEAD', baseURL + '/' + PROJECT.songs[NOW].file);
		xhr.addEventListener('readystatechange', function(){
			if (xhr.readyState == XMLHttpRequest.DONE) {
				if (xhr.status == 200) {
					/^filename="([\s\S]+)"/.test(xhr.getResponseHeader('Content-Disposition'));
					document.getElementById('choosedFile'). innerHTML = RegExp.$1;
				} else {
					document.getElementById('choosedFile'). innerHTML = 'Une erreur ' + xhr.status + ' : ' + xhr.statusText + ' nous empèche de savoir quel est le fichier choisi';
				}
			}
		});
		xhr.send(null);
	} else {
		document.getElementById('choosedFile'). innerHTML = 'Choisissez un fichier';
	}
}

function edit(elementId) {
	if (editing) {
		endEdit();
	}
	editing = elementId;
	console.log('Élément ' + elementId + ' est en cours d\'édition');
	var element = document.getElementById(elementId),
		contenu = element.innerHTML;
	var input = document.createElement('input');
	if (elementId == 'songVol') {
		input.type = 'range';
		input.step = 0.01;
		input.min = 0;
		input.max = 1;
		input.value = parseFloat(contenu, 10);
	} else if (/Descr$/.test(elementId) && element.className == 'no-descr') {
		input.type = 'text';
	} else {
		input.type = 'text';
		input.value = contenu;
	}
	(function(contenu, input) {
		input.addEventListener('blur', endEdit);
		input.addEventListener('keydown', function(e) {
			if (e.keyCode == 27) {
				editing = false;
				input.parentNode.innerHTML = contenu;
				console.log('Modifications annulées');
			} else if (e.keyCode == 13) {
				input.removeEventListener('blur', endEdit);
				endEdit();
			}
		});
	})(contenu, input);
	element.innerHTML = '';
	element.appendChild(input);
	input.select();
}

function chooseFile() {
	if (editing) {
		endEdit();
	}
	editing = 'file';

	var popUp = document.getElementById('popUp'),
		frame = document.getElementById('popUpModif'),
		popUpButtons = document.getElementById('popUpButtons');

	frame.innerHTML = '';
	popUpButtons.innerHTML = '';

	//boutons
	var buttons = [
			document.createElement('input'),
			document.createElement('input')
		],
		text = [
			'Mes fichiers',
			'Importer'
		];

	buttons[0].value = text[0];
	buttons[1].value = text[1];
	buttons[0].type  = 'button';
	buttons[1].type  = 'button';

	buttons[0].addEventListener('click', chooseMyFiles);

	// formulaire d'upload
	buttons[1].addEventListener('click', function() {
		/*var xhr = new XMLHttpRequest();
		xhr.open('GET', baseURL.replace(/get$/, 'add'), false);
		xhr.send(null);*/

		var place = document.getElementById('popUpModif');
		place.innerHTML = '';

		/*if (xhr.status != 200) {
			place.appendChild(document.createTextNode('Erreur ' + xhr.status + ' : ' + xhr.statusText));
		} else {
			place.innerHTML = xhr.responseText;
		}*/

		var iframe = document.createElement('iframe');
		iframe.src = baseURL.replace(/get$/, 'add');
		place.appendChild(iframe);
	});

	popUpButtons.appendChild(buttons[0]);
	popUpButtons.appendChild(buttons[1]);

	popUp.style.display = 'block';

	console.log('Choix d\'un fichier');
	chooseMyFiles();
}

function chooseMyFiles() {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', baseURL + '/mine', false);
	xhr.send(null);
	var place = document.getElementById('popUpModif');
	place.innerHTML = '';
	if (xhr.status != 200) {
		place.appendChild(document.createTextNode('Erreur ' + xhr.status + ' : ' + xhr.statusText));
	} else {
		var response = JSON.parse(xhr.responseText);

		var TRs = new Array();
		for (var i = response.length - 1; i >= 0; i--) {
			var TDs = [
					document.createElement('td'),
					document.createElement('td'),
					document.createElement('td')
				],
				radio = document.createElement('input'),
				link  = document.createElement('a'),
				tr    = document.createElement('tr');
			
			radio.type  = 'radio';
			radio.name  = 'file';
			radio.value = response[i].id;
			link.innerHTML = 'Supprimer';
			(function(id, tr, place){
				link.onclick = function() {
					if (confirm('Toute suppression est définitive,\nVoulez vous supprimer ce son?')) {
						xhr = new XMLHttpRequest();
						xhr.open('GET', baseURL.replace(/get$/, 'remove/') + id);
						xhr.addEventListener('readystatechange', function() {
							if (xhr.readyState == XMLHttpRequest.DONE){
								if (xhr.status != 200) {
									alert(
										'Le fichier n\'a pas pu être supprimé.\n\nIl est peut-être employé par un autre projet.'
										);
								} else {
									place.getElementsByTagName('table')[0].removeChild(tr);
								}
							}
						});
						xhr.send(null);
					}
				}
			})(response[i].id, tr, place)
			TDs[0].appendChild(radio);
			TDs[1].innerHTML = response[i].name;
			TDs[2].appendChild(link);
			if (PROJECT.songs[NOW].file == response[i].id)
				radio.checked = 'checked';

			tr.appendChild(TDs[0]);
			tr.appendChild(TDs[1]);
			tr.appendChild(TDs[2]);
			TRs.push(tr);
		}
		if (!response.length) {
			var tr   = document.createElement('tr'),
				td   = document.createElement('td'),
				text = document.createTextNode('Vous n\'avez pas encore de fichiers');
			tr.appendChild(td);
			td.appendChild(text);
			td.setAttribute('colspan', 2);
			TRs.push(tr);
		}

		var table = place.appendChild(document.createElement('table'));
		table.innerHTML = '<tr><th></th><th>Titre</th></tr>';
		for (var i = TRs.length - 1; i >= 0; i--) {
			table.appendChild(TRs[i]);
		}
	}
}

function chooseTrans() {
	if (editing) {
		endEdit();
	}
	editing = 'trans';

	var popUp = document.getElementById('popUp'),
		frame = document.getElementById('popUpModif'),
		buttons = document.getElementById('popUpButtons');

	//vidange du pop-up
	frame.innerHTML = '';
	buttons.innerHTML = '';

	//création des éléments
	var 
		selects = [
			document.createElement('select'),
			document.createElement('select'),
			document.createElement('select')
		],
		labels = [
			document.createElement('label'),
			document.createElement('label'),
			document.createElement('label'),
			document.createElement('label'),
			document.createElement('label')
		]
		types = [
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option')
		],
		trans1 = [
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option')
		],
		trans2 = [
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option'),
			document.createElement('option')
		],
		auto = [
			document.createElement('input'),
			document.createElement('input')
		]
	//conf par défaut
	for (var i = types.length - 1; i >= 0; i--) {
		selects[0].appendChild(types[i]);
	}
	for (var i = trans1.length - 1; i >= 0; i--) {
		selects[1].appendChild(trans1[i]);
	}
	for (var i = trans2.length - 1; i >= 0; i--) {
		selects[2].appendChild(trans2[i]);
	}
	types[0].innerHTML = 'Random';
	types[1].innerHTML = 'Restart';
	types[2].innerHTML = 'Repeat';
	types[3].innerHTML = 'One';
	trans1[0].innerHTML = trans2[0].innerHTML = 'Full';
	trans1[1].innerHTML = trans2[1].innerHTML = 'Longfadeout';
	trans1[2].innerHTML = trans2[2].innerHTML = 'Fadeout';
	trans1[3].innerHTML = trans2[3].innerHTML = 'Quickfadeout';
	trans1[4].innerHTML = trans2[4].innerHTML = 'Raw';
	trans1[5].innerHTML = 'Aucune';
	types[0].value = '#';
	types[1].value = '&';
	types[2].value = 'O';
	types[3].value = '1';
	trans1[0].value = trans2[0].value = 'f';
	trans1[1].value = trans2[1].value = 'l';
	trans1[2].value = trans2[2].value = 's';
	trans1[3].value = trans2[3].value = 'q';
	trans1[4].value = trans2[4].value = 'r';
	trans1[5].value = '';
	selects[0].name = 'type';
	selects[1].name = 'trans1';
	selects[2].name = 'trans2';
	auto[0].type = auto[1].type = 'checkbox';
	auto[0].id = 'auto1';
	auto[1].id = 'auto2'
	labels[0].innerHTML = 'Type ';
	labels[1].innerHTML = 'Transition 1 ';
	labels[2].innerHTML = 'Autonext 1 ';
	labels[3].innerHTML = 'Transition 2 ';
	labels[4].innerHTML = 'Autonext 2 ';

	//actuel
	var SONG = PROJECT.songs[NOW];
		 if (SONG.trans[0] == '1') types[3].selected = true;
	else if (SONG.trans[0] == '&') types[1].selected = true;
	else if (SONG.trans[0] == '#') types[0].selected = true;
	else if (SONG.trans[0] == 'O') types[2].selected = true;
		 if (~SONG.trans[1].indexOf('q')) trans1[3].selected = true;
	else if (~SONG.trans[1].indexOf('s')) trans1[2].selected = true;
	else if (~SONG.trans[1].indexOf('l')) trans1[1].selected = true;
	else if (~SONG.trans[1].indexOf('f')) trans1[0].selected = true;
	else if (~SONG.trans[1].indexOf('r')) trans1[4].selected = true;
	else if (SONG.trans[1].length == 0) trans1[5].selected = true;
		 if (~SONG.trans[2].indexOf('q')) trans2[3].selected = true;
	else if (~SONG.trans[2].indexOf('s')) trans2[2].selected = true;
	else if (~SONG.trans[2].indexOf('l')) trans2[1].selected = true;
	else if (~SONG.trans[2].indexOf('f')) trans2[0].selected = true;
	else if (~SONG.trans[2].indexOf('r')) trans2[4].selected = true;
	if (~SONG.trans[1].indexOf('n')) auto[0].checked = true;
	if (~SONG.trans[2].indexOf('n')) auto[1].checked = true;

	//insertion
	frame.appendChild(labels[0]);
	frame.appendChild(selects[0]);
	frame.appendChild(document.createElement('br'));
	frame.appendChild(labels[1]);
	frame.appendChild(selects[1]);
	frame.appendChild(document.createElement('br'));
	frame.appendChild(labels[2]);
	frame.appendChild(auto[0]);
	frame.appendChild(document.createElement('br'));
	frame.appendChild(labels[3]);
	frame.appendChild(selects[2]);
	frame.appendChild(document.createElement('br'));
	frame.appendChild(labels[4]);
	frame.appendChild(auto[1]);

	//affichage
	popUp.style.display = 'block';

	console.log('Choix d\'une transition');
}

function endEdit() {
	if (editing == false) return;
	switch (editing) {
		case 'trans':
			document.getElementById('popUp').style.display = 'none';

			var type = document.querySelector('select[name="type"]').querySelector('option:checked').value,
				trans1 = document.querySelector('select[name="trans1"]').querySelector('option:checked').value,
				trans2 = document.querySelector('select[name="trans2"]').querySelector('option:checked').value;
			if (document.getElementById('auto1').checked) trans1 += 'n';
			if (document.getElementById('auto2').checked) trans2 += 'n';
			
			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification de transition a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('lineId=' + PROJECT.songs[NOW].id + '&trans1=' + trans1 + '&trans2=' + trans2 + '&type=' + type);

			PROJECT.songs[NOW].trans[0] = type;
			PROJECT.songs[NOW].trans[1] = trans1;
			PROJECT.songs[NOW].trans[2] = trans2;

			document.getElementById('choosedTrans').innerHTML = songtype_to_str(PROJECT.songs[NOW]);
			document.getElementById('songList').getElementsByTagName('span')[NOW].innerHTML = '(' + songtype_to_str(PROJECT.songs[NOW]) + ')';
			break;

		case 'file':
			document.getElementById('popUp').style.display = 'none';
			
			var selected = document.getElementById('popUpModif').querySelector('input[type="radio"]:checked');
			var selectedId = selected.value;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification de fichier a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('lineId=' + PROJECT.songs[NOW].id + '&file=' + selectedId);

			PROJECT.songs[NOW].file = selectedId;
			document.
				getElementById('choosedFile').
				innerHTML = selected.
					parentNode.
					nextElementSibling.
					innerHTML;
			break;

		case 'songName':
			var element = document.getElementById('songName'),
				contenu = element.firstElementChild.value;

			// évite les titres vides
			if (contenu.length == 0) {
				element.firstElementChild.addEventListener('blur', endEdit);
				alert('Le titre ne peut pas être vide.');
				element.firstElementChild.select();
				return;
			}

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('lineId=' + PROJECT.songs[NOW].id + '&songName=' + contenu);

			document.getElementById('songList').getElementsByTagName('song')[NOW].innerHTML = contenu;
			PROJECT.songs[NOW].name = contenu;
			element.innerHTML = contenu;
			break;

		case 'songDescr':
			var element = document.getElementById('songDescr'),
				contenu = element.firstElementChild.value;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('lineId=' + PROJECT.songs[NOW].id + '&songDescr=' + contenu);

			document.getElementById('songList').getElementsByTagName('descr')[NOW].innerHTML = contenu;
			PROJECT.songs[NOW].descr = contenu;
			if (contenu.length) {
				element.innerHTML = contenu;
				element.className = '';
			} else {
				element.innerHTML = 'Pas de description';
				element.className = 'no-descr'
			}
			break;

		case 'songVol':
			var element = document.getElementById('songVol'),
				contenu = element.firstElementChild.value;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('lineId=' + PROJECT.songs[NOW].id + '&songVol=' + contenu);

			PROJECT.songs[NOW].name = parseFloat(contenu, 10);
			element.innerHTML = contenu;
			break;

		case 'projTitle':
			var element = document.getElementById('projTitle'),
				contenu = element.firstElementChild.value;

			// évite les titres vides
			if (contenu.length == 0) {
				element.firstElementChild.addEventListener('blur', endEdit);
				alert('Le titre ne peut pas être vide.');
				element.firstElementChild.select();
				return;
			}

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('projTitle=' + contenu);

			var title = document.getElementsByTagName('title')[0];
			title.innerHTML = title.innerHTML.replace(/([\S\s]+?) –/, contenu + ' –');
			PROJECT.name = contenu;
			element.innerHTML = contenu;
			break;

		case 'projDescr':
			var element = document.getElementById('projDescr'),
				contenu = element.firstElementChild.value;

			var xhr = new XMLHttpRequest();
			xhr.open('POST', document.location.href);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.addEventListener('readystatechange', function() {
				if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 202) {
					alert('La modification a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
				}
			});
			xhr.send('projDescr=' + contenu);

			PROJECT.descr = contenu;
			if (contenu.length) {
				element.innerHTML = contenu;
				element.className = '';
			} else {
				element.innerHTML = 'Pas de description';
				element.className = 'no-descr'
			}
			break;
	}

	editing = false;
	console.log('Édition terminée');
}

function addSong() {
	if (editing) {
		endEdit();
	}

	var xhr = new XMLHttpRequest();
	xhr.open('POST', document.location.href);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.addEventListener('readystatechange', function(e) {
		if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 201/*HTTP_CREATED*/) {
			var response = JSON.parse(xhr.responseText);
			PROJECT.songs.push(response);

			var li = document.createElement('li'),
				song = document.createElement('song'),
				descr = document.createElement('descr'),
				span = document.createElement('span');
			song.innerHTML = response.name;
			descr.innerHTML = ' '+response.descr;
			span.innerHTML = ' ('+songtype_to_str(response)+')';
			li.appendChild(song);
			li.appendChild(descr);
			li.appendChild(span);
			load_song_listener(PROJECT.songs.length - 1, li);
			document.getElementById('songList').appendChild(li);
			load_song(PROJECT.songs.length - 1);
		} if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 201) {
			alert('L\'ajout du son a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
		}
	});
	xhr.send('addSong=true');
	
	console.log('Ajout d\'un son');
}

function removeThisSong() {
	if (editing) {
		endEdit();
	}
	if (PROJECT.songs.length < 2) {
		console.error('Attempted to remove the only song of this project');
		alert('Vous ne pouvez pas retirer le seul son du projet!');
		return;
	}
	var xhr = new XMLHttpRequest();
	xhr.open('POST', document.location.href);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.addEventListener('readystatechange', function(e) {
		if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200/*HTTP_OK*/) {
			var list = document.getElementById('songList');
			list.removeChild(list.getElementsByTagName('li')[NOW]);
			PROJECT.songs.splice(NOW, 1);
			if (NOW != 0) {
				load_song(NOW - 1);
			} else {
				load_song(0);
			}
			var LIs = list.getElementsByTagName('li');
			for (var i = LIs.length - 1; i >= 0; i--) {
				load_song_listener(i, LIs[i]);
			}
		} else if (xhr.readyState == XMLHttpRequest.DONE && xhr.status != 200) {
			alert('La suppression du son a échoué suite à une erreur ' + xhr.status + ': ' + xhr.responseText);
		}
	});
	xhr.send('removeSong=' + PROJECT.songs[NOW].id);
	
	console.log('Retrait du son n°' + PROJECT.songs[NOW].id);
}

(function() {
	document.getElementById('fermerAffichage').addEventListener('click', function(event) {
		if (confirm('Les modifications ne sont pas sauvegardées.\nVoulez vous vraiment quitter?')) {
			document.getElementById('popUp').style.display = 'none';
			editing = false;
			console.log('Modifications annulées.');
		}
	});
	window.onbeforeunload = function() {
		if (editing) {
			endEdit();
			return '';
		}
	}
})();