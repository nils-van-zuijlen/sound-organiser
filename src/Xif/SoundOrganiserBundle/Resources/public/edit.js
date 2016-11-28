// src/Xif/SoundOrganiserBundle/Resources/public/edit.js

/**
 * Hydraté via PHP depuis la BDD dans le template HTML
 * @var array PROJECT Projet complet
 */
var PROJECT;

/**
 * Numéro du son actuel
 * @var {int}
 */
var NOW;

/**
 * @var {boolean} En train d'éditer
 */
var editing = false;

/**
 * URL du dossier contenant les sons
 * @var {string}
 */
var baseURL;

function load_song_listener(i, LI) {
	$('#songList li').each(function(index) {
		$(this).off('click.Xif');
		$(this).on('click.Xif', function() {
			load_song(index);
		});
	});
}

function open_proj() {
	///affichage
	//transitions de la liste
	var listElements = $('#songList li');
	listElements.each(
		function (index) {
			this
				.getElementsByTagName('span')[0]
				.innerHTML = '(' + songtype_to_str(PROJECT.songs[index]) + ')';
		}
	);

	//transition du son affiché
	$('#choosedTrans').html(songtype_to_str(PROJECT.songs[0]));

	///évenements
	$('#projTitle').on('dblclick.Xif', function(){edit('projTitle');});
	$('#projDescr').on('dblclick.Xif', function(){edit('projDescr');});
	$('#songName').on('dblclick.Xif', function(){edit('songName');});
	$('#songDescr').on('dblclick.Xif', function(){edit('songDescr');});
	$('#songVol').on('dblclick.Xif', function(){edit('songVol');});
	$('#chooseFile').on('click.Xif', chooseFile);
	$('#chooseTrans').on('click.Xif', chooseTrans);
	load_song_listener();

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

	$('#songList li').removeClass('list-group-item-info');
	$($('#songList li')[NOW]).addClass('list-group-item-info');
	$('#songName').html(SONG.name);
	if (SONG.descr.length) {
		$('#songDescr').html(SONG.descr);
		$('#songDescr').removeClass('help-block');
	} else {
		$('#songDescr').html('Pas de description');
		$('#songDescr').addClass('help-block');
	}
	$('#choosedTrans').html(songtype_to_str(SONG));
	$('#songVol').html(SONG.vol);
	getFileName();
}

function getFileName() {
	if (PROJECT.songs[NOW].file){
		$('#choosedFile').load(baseURL + '/' + PROJECT.songs[NOW].file+'/name');
	} else {
		$('#choosedFile').text('Choisissez un fichier');
	}
}

function edit(elementId) {
	if (editing) {
		endEdit();
	}
	editing = elementId;
	console.log('Élément ' + elementId + ' est en cours d\'édition');
	var element = $('#'+elementId),
		contenu = element.text();
	var input = $('<input class="form-control" />'),
		toAppend;
	if (elementId == 'songVol') {
		input
			.attr({
				type: 'range',
				step: 0.01,
				min: 0,
				max: 1,
				value: parseFloat(contenu, 10)
			})
			.removeClass('form-control')
			.css('margin-left', '1em');
		toAppend = $('<div></div>');
		var value = $('<span id="volEditValue">'+contenu+'</span>');
		toAppend.append(value);
		toAppend.append(input);
		toAppend.css('display', 'flex');
		value.css();
		input.on('change.Xif', function() {
			$('#volEditValue').html(input.val());
		});
	} else if (/Descr$/.test(elementId) && element.hasClass('help-block')) {
		input.attr('type', 'text');
		element.removeClass('help-block');
		toAppend = input;
	} else {
		input.attr({
			type: 'text',
			value: contenu
		});
		toAppend = input;
	}
	(function(contenu, input) {
		//input.on('blur.Xif', endEdit);
		input.on('keydown.Xif', function(e) {
			if (e.keyCode == 27) {
				editing = false;
				input.get().parentNode.innerHTML = contenu;
				console.log('Modifications annulées');
			} else if (e.keyCode == 13) {
				input.off('blur.Xif', endEdit);
				endEdit();
			}
		});
	})(contenu, input);
	element.html('');
	element.append(toAppend);
	input.focus();
}

function chooseFile() {
	if (editing) {
		endEdit();
	}
	editing = 'file';

	var popUp = $('#popUp'),
		frame = $('#popUpModif'),
		popUpButtons = $('#popUpButtons');

	frame.html('');
	popUpButtons.html('');

	//boutons
	var buttons = [
			//classes btn-primary and btn-default will be inverted in @func chooseMyFiles()
			$('<input type="button" class="btn btn-default" value="Mes fichiers">'),
			$('<input type="button" class="btn btn-primary" value="Importer">')
		],
		div = $('<div class="btn-group"></div>');

	buttons[0].on('click.Xif', chooseMyFiles);

	// formulaire d'upload
	buttons[1].on('click.Xif', function() {
		var place = $('#popUpModif');
		place.html('');
		$('#popUpButtons input').toggleClass('btn-primary btn-default');

		var iframe = $('<iframe></iframe>');
		iframe.attr('src', baseURL.replace(/get$/, 'add'));
		place.append(iframe);
	});

	div.append(buttons[0]).append(buttons[1]);
	popUpButtons.append(div);

	popUp.modal('show');

	console.log('Choix d\'un fichier');
	chooseMyFiles();
}

function chooseMyFiles(reload) {
	$('#popUpModif').html('');
	if (typeof reload == "undefined")
		$('#popUpButtons input').toggleClass('btn-primary btn-default');

	$.ajax({
		url: baseURL+'/mine',
		type: 'GET',
		dataType: 'json',
		error: function() {
			$('#popUpModif').append($('<p>Erreur '+xhr.status +' : '+xhr.statusText+'</p>'));
		},
		success: function(response) {
			var TRs = new Array(),
				place = $('#popUpModif');
			for (var i = response.length - 1; i >= 0; i--) {
				var TDs = [
						$('<td></td>'),
						$('<td id="file'+response[i].id+'">'+response[i].name+'</td>'),
						$('<td></td>')
					],
					radio = $('<input type="radio" name="file" value="'+response[i].id+'">'),
					link  = $('<button>Supprimer</button>'),
					tr    = $('<tr></tr>');

				(function(id, tr, place){
					link.on('click.Xif', function() {
						if (confirm('Toute suppression est définitive,\nVoulez vous supprimer ce son?')) {
							$.ajax({
								url: baseURL.replace(/get$/, 'remove/') + id,
								dataType: 'text',
								error: function(){
									alert(
										'Le fichier n\'a pas pu être supprimé.\n\nIl est sans-doute employé par un autre projet.'
										);
								},
								success: function(){
									tr.remove();
									chooseMyFiles(true);
								}
							});
						}
					});
				})(response[i].id, tr, place)

				TDs[0].append(radio);
				TDs[2].append(link);
				if (PROJECT.songs[NOW].file == response[i].id)
					radio.prop('checked', true);

				tr.append(TDs[0]);
				tr.append(TDs[1]);
				tr.append(TDs[2]);
				TRs.push(tr);
			}
			if (!response.length) {
				var tr   = $('<tr><td colspan=2>Vous n\'avez pas encore de fichiers</td></tr>');
				TRs.push(tr);
			}

			var table = $('<table class="table"></table>');
			place.append(table)
			table.html('<tr><th></th><th>Titre</th></tr>');
			for (var i = TRs.length - 1; i >= 0; i--) {
				table.append(TRs[i]);
			}
		}
	});
}

function chooseTrans() {
	if (editing) {
		endEdit();
	}
	editing = 'trans';

	var popUp = $('#popUp'),
		frame = $('#popUpModif'),
		buttons = $('#popUpButtons');

	//vidange du pop-up
	frame.html('');
	buttons.html('');

	//création des éléments
	var 
		selects = [
			$('<select name="type" class="form-control"><option value="#">Random</option><option value="&">Restart</option><option value="O">Repeat</option><option value="1">One</option></select>'),
			$('<select name="trans1" class="form-control"><option value="f">Full</option><option value="l">Longfadeout</option><option value="s">Fadeout</option><option value="q">Quickfadeout</option><option value="r">Raw</option><option value="">Aucune</option></select>'),
			$('<select name="trans2" class="form-control"><option value="f">Full</option><option value="l">Longfadeout</option><option value="s">Fadeout</option><option value="q">Quickfadeout</option><option value="r">Raw</option></select>')
		],
		labels = [
			$('<label>Type </label>'),
			$('<label>Transition 1 </label>'),
			$('<label>Autonext 1 </label>'),
			$('<label>Transition 2 </label>'),
			$('<label>Autonext 2 </label>')
		]
		auto = [
			$('<input type="checkbox" id="auto1">'),
			$('<input type="checkbox" id="auto2">')
		];

	//insertion
		frame.append(labels[0]);
		frame.append(selects[0]);
		frame.append($('<br>'));
		frame.append(labels[1]);
		frame.append(selects[1]);
		frame.append($('<br>'));
		frame.append(labels[2]);
		frame.append(auto[0]);
		frame.append($('<br>'));
		frame.append(labels[3]);
		frame.append(selects[2]);
		frame.append($('<br>'));
		frame.append(labels[4]);
		frame.append(auto[1]);

	//actuel
	var SONG = PROJECT.songs[NOW];
		 if (SONG.trans[0] == '1') $('[name="type"]>[value="1"]').prop('selected', true);
	else if (SONG.trans[0] == '&') $('[name="type"]>[value="&"]').prop('selected', true);
	else if (SONG.trans[0] == '#') $('[name="type"]>[value="#"]').prop('selected', true);
	else if (SONG.trans[0] == 'O') $('[name="type"]>[value="O"]').prop('selected', true);
		 if (~SONG.trans[1].indexOf('q')) $('[name="trans1"]>[value="q"]').prop('selected', true)
	else if (~SONG.trans[1].indexOf('s')) $('[name="trans1"]>[value="s"]').prop('selected', true)
	else if (~SONG.trans[1].indexOf('l')) $('[name="trans1"]>[value="l"]').prop('selected', true)
	else if (~SONG.trans[1].indexOf('f')) $('[name="trans1"]>[value="f"]').prop('selected', true)
	else if (~SONG.trans[1].indexOf('r')) $('[name="trans1"]>[value="r"]').prop('selected', true)
	else if (SONG.trans[1].length == 0)   $('[name="trans1"]>[value=""]').prop('selected', true)
		 if (~SONG.trans[2].indexOf('q')) $('[name="trans2"]>[value="q"]').prop('selected', true)
	else if (~SONG.trans[2].indexOf('s')) $('[name="trans2"]>[value="s"]').prop('selected', true)
	else if (~SONG.trans[2].indexOf('l')) $('[name="trans2"]>[value="l"]').prop('selected', true)
	else if (~SONG.trans[2].indexOf('f')) $('[name="trans2"]>[value="f"]').prop('selected', true)
	else if (~SONG.trans[2].indexOf('r')) $('[name="trans2"]>[value="r"]').prop('selected', true)
	if (~SONG.trans[1].indexOf('n')) $('#auto1').prop('checked', true);
	if (~SONG.trans[2].indexOf('n')) $('#auto2').prop('checked', true);

	//affichage
	popUp.modal('show');

	console.log('Choix d\'une transition');
}

function endEdit() {
	if (editing === false) return;
	switch (editing) {
		case 'trans':
			$('#popUp').modal('hide');

			var type = $('select[name="type"]>option:selected').val(),
				trans1 = $('select[name="trans1"]>option:selected').val(),
				trans2 = $('select[name="trans2"]>option:selected').val();
			if ($('#auto1').prop('checked')) trans1 += 'n';
			if ($('#auto2').prop('checked')) trans2 += 'n';
			
			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: {
					lineId: PROJECT.songs[NOW].id,
					trans1: trans1,
					trans2: trans2,
					type: type
				},
				dataType: 'text',
				error: function(){
					alert('La modification de transition a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			PROJECT.songs[NOW].trans[0] = type;
			PROJECT.songs[NOW].trans[1] = trans1;
			PROJECT.songs[NOW].trans[2] = trans2;

			$('#choosedTrans').html(songtype_to_str(PROJECT.songs[NOW]));
			$($('#songList span')[NOW]).html('(' + songtype_to_str(PROJECT.songs[NOW]) + ')');
			break;

		case 'file':
			$('#popUp').modal('hide');
			
			var selected = $('#popUpModif input[type="radio"]:checked');
			var selectedId = selected.val();

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: {
					lineId: PROJECT.songs[NOW].id,
					file: selectedId
				},
				dataType: 'text',
				error: function(){
					alert('La modification de fichier a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			PROJECT.songs[NOW].file = selectedId;
			$('#choosedFile').html($('#file'+selectedId).text());
			break;

		case 'songName':
			var element = $('#songName'),
				input   = $('#songName input'),
				contenu = input.val();

			// évite les titres vides
			if (contenu.length == 0) {
				input.on('blur.Xif', endEdit);
				alert('Le titre ne peut pas être vide.');
				input.focus();
				return;
			}

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: {
					lineId: PROJECT.songs[NOW].id,
					songName: contenu
				},
				dataType: 'text',
				error: function() {
					alert('La modification a échoué suite à une erreur. '/* +xhr.status+': '+xhr.responseText */);
				}
			});

			$($('#songList song')[NOW]).html(contenu);
			PROJECT.songs[NOW].name = contenu;
			element.html(contenu);
			break;

		case 'songDescr':
			var element = $('#songDescr'),
				input   = $('#songDescr input')
				contenu = input.val();

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: {
					lineId: PROJECT.songs[NOW].id,
					songDescr: contenu
				},
				dataType: 'text',
				error: function(){
					alert('La modification a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			$($('#songList descr')[NOW]).html(contenu);
			PROJECT.songs[NOW].descr = contenu;
			if (contenu.length) {
				element.html(contenu);
				element.removeClass('help-block');
			} else {
				element.html('Pas de description');
				element.addClass('help-block');
			}
			break;

		case 'songVol':
			var element = $('#songVol'),
				input   = $('#songVol input'),
				contenu = input.val();

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: {
					lineId: PROJECT.songs[NOW].id,
					songVol: contenu
				},
				dataType: 'text',
				error: function(){
					alert('La modification a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			PROJECT.songs[NOW].name = parseFloat(contenu, 10);
			element.html(contenu);
			break;

		case 'projTitle':
			var element = $('#projTitle'),
				input   = $('#projTitle input'),
				contenu = input.val();

			// évite les titres vides
			if (contenu.length == 0) {
				input.on('blur.Xif', endEdit);
				alert('Le titre ne peut pas être vide.');
				input.focus();
				return;
			}

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: { projTitle: contenu },
				dataType: 'text',
				error: function(){
					alert('La modification a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			var title = $('title:eq(0)');
			title.html(title.html().replace(/^([\S\s]+?) –/, contenu + ' –'));
			PROJECT.name = contenu;
			element.html(contenu);
			break;

		case 'projDescr':
			var element = $('#projDescr'),
				input   = $('#projDescr input'),
				contenu = input.val();

			$.ajax({
				url: document.location.href,
				type: 'POST',
				data: { projDescr: contenu },
				dataType: 'text',
				error: function(){
					alert('La modification a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
				}
			});

			PROJECT.descr = contenu;
			if (contenu.length) {
				element.html(contenu);
				element.removeClass('help-block');
			} else {
				element.html('Pas de description');
				element.addClass('help-block');
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

	var xhr = new XMLHttpRequest;
	xhr.open('POST', document.location.href);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.addEventListener('readystatechange', function(event) {
		if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
			var response = JSON.parse(xhr.responseText);
			PROJECT.songs.push(response);

			var li = $('<li class="list-group-item"><song>'+response.name+'</song> <descr>'+response.descr+'</descr> <span>('+songtype_to_str(response)+')</span></li>');

			load_song_listener(PROJECT.songs.length - 1, li);
			$('#songList').append(li);
			load_song(PROJECT.songs.length - 1);
		} else if (xhr.readyState === XMLHttpRequest.DONE && xhr.status != 200) {
			console.error('Erreur lors de l\'ajout du son');
			alert('Erreur lors de l\'ajout du son');
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
	
	$.ajax({
		url: document.location.href,
		type: 'POST',
		data: 'removeSong='+PROJECT.songs[NOW].id,
		dataType: 'text',
		error: function(){
			alert('La suppression du son a échoué suite à une erreur. '/* + xhr.status + ': ' + xhr.responseText*/);
		},
		success: function (){
			$('#songList li:eq('+NOW+')').remove();
			PROJECT.songs.splice(NOW, 1);
			if (NOW != 0) {
				load_song(NOW - 1);
			} else {
				load_song(0);
			}
			load_song_listener();
		}
	});
	
	console.log('Retrait du son n°' + PROJECT.songs[NOW].id);
}

$(function() {
	$('#popUp').on('hidden.bs.modal', function(event) {
		editing = false;
		console.log('Modifications annulées.');
	});
	$(window).on('beforeunload.Xif', function() {
		if (editing) {
			endEdit();
			return '';
		}
	});
});
