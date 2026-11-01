/**
 * Réservation d’un service : calendrier, créneaux, passage au formulaire.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-afvf-booking]');
	if (!root) {
		return;
	}

	var conf;
	try {
		conf = JSON.parse(root.getAttribute('data-afvf-booking'));
	} catch (e) {
		return;
	}

	var avail = conf.avail || {};
	var calEl = root.querySelector('[data-cal]');
	var slotsEl = root.querySelector('[data-slots]');
	var nextBtn = root.querySelector('[data-next]');
	var stepPick = root.querySelector('[data-step="1"]');
	var stepForm = root.querySelector('[data-step="2"]');
	var details = root.querySelector('.afvf-bk__details');
	var backLink = root.querySelector('[data-afvf-back]');
	var fDate = root.querySelector('[data-f-date]');
	var fTime = root.querySelector('[data-f-time]');
	var recap = root.querySelector('[data-recap]');
	var titleEl = root.querySelector('[data-title]');
	var introEl = root.querySelector('[data-intro]');
	var tzEl = root.querySelector('[data-tz]');

	var DAYS = ['lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'];
	var keys = Object.keys(avail).sort();

	function parse(s) {
		var p = s.split('-');
		return new Date(+p[0], +p[1] - 1, +p[2]);
	}
	function fmt(d) {
		var m = d.getMonth() + 1;
		var j = d.getDate();
		return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (j < 10 ? '0' : '') + j;
	}
	function cap(s) {
		return s.charAt(0).toUpperCase() + s.slice(1);
	}
	function longDate(s, withYear) {
		var o = { weekday: 'long', day: 'numeric', month: 'long' };
		if (withYear) {
			o.year = 'numeric';
		}
		return parse(s).toLocaleDateString('fr-FR', o);
	}

	var today = conf.today;
	var first = parse(today);
	var last = parse(conf.horizon);
	var month = new Date(first.getFullYear(), first.getMonth(), 1);
	var selDate = today;
	var selTime = '';
	var showAll = false;
	var LIMIT = 9;

	function renderCal() {
		var y = month.getFullYear();
		var m = month.getMonth();
		var label = cap(month.toLocaleDateString('fr-FR', { month: 'long' })) + ' ' + y;
		var canPrev = month > new Date(first.getFullYear(), first.getMonth(), 1);
		var canNext = new Date(y, m + 1, 1) <= last;

		var h = '<div class="afvf-cal__bar">' +
			'<button type="button" class="afvf-cal__nav" data-nav="-1" aria-label="Mois précédent"' + (canPrev ? '' : ' disabled') + '>‹</button>' +
			'<span class="afvf-cal__label">' + label + '</span>' +
			'<button type="button" class="afvf-cal__nav" data-nav="1" aria-label="Mois suivant"' + (canNext ? '' : ' disabled') + '>›</button>' +
			'</div><table class="afvf-cal__grid"><thead><tr>';
		DAYS.forEach(function (d) {
			h += '<th scope="col">' + d + '</th>';
		});
		h += '</tr></thead><tbody>';

		var offset = (new Date(y, m, 1).getDay() + 6) % 7;
		var total = new Date(y, m + 1, 0).getDate();
		var day = 1 - offset;
		while (day <= total) {
			h += '<tr>';
			for (var c = 0; c < 7; c++, day++) {
				if (day < 1 || day > total) {
					h += '<td></td>';
					continue;
				}
				var key = fmt(new Date(y, m, day));
				var has = !!avail[key];
				var cls = 'afvf-cal__day';
				if (key < today) {
					cls += ' is-past';
				}
				if (has) {
					cls += ' is-avail';
				}
				if (key === selDate) {
					cls += ' is-sel';
				}
				h += '<td><button type="button" class="' + cls + '" data-date="' + key + '"' + (key < today ? ' disabled' : '') + '>' + day + (has ? '<i aria-hidden="true"></i>' : '') + '</button></td>';
			}
			h += '</tr>';
		}
		h += '</tbody></table>';
		calEl.innerHTML = h;
	}

	function renderSlots() {
		var list = avail[selDate] || [];
		var h = '<p class="afvf-bk__avail">' + conf.i18n.avail + ' ' + longDate(selDate, false) + '</p>';
		if (!list.length) {
			h += '<p class="afvf-bk__none">' + conf.i18n.none + '</p>';
			var nxt = null;
			for (var i = 0; i < keys.length; i++) {
				if (keys[i] > selDate) {
					nxt = keys[i];
					break;
				}
			}
			if (nxt) {
				h += '<button type="button" class="afvf-bk__nextdate" data-goto="' + nxt + '">Prochaine date disponible</button>';
			}
		} else {
			var shown = showAll ? list : list.slice(0, LIMIT);
			// Le créneau choisi reste toujours visible.
			if (!showAll && selTime && shown.indexOf(selTime) < 0) {
				shown = list;
			}
			h += '<div class="afvf-bk__times">';
			shown.forEach(function (t) {
				h += '<button type="button" class="afvf-bk__time' + (t === selTime ? ' is-sel' : '') + '" data-time="' + t + '">' + t + '</button>';
			});
			h += '</div>';
			if (list.length > LIMIT) {
				h += '<p class="afvf-bk__allwrap"><button type="button" class="afvf-bk__all" data-all>' + (shown.length === list.length ? 'Voir moins de séances' : 'Voir toutes les séances') + '</button></p>';
			}
		}
		slotsEl.innerHTML = h;
		nextBtn.disabled = !selTime;
		nextBtn.classList.toggle('afvf-bk__next--on', !!selTime);
	}

	function chooseDay(key) {
		selDate = key;
		showAll = false;
		selTime = (avail[key] && avail[key][0]) || '';
	}

	function pickDate(key) {
		chooseDay(key);
		var d = parse(key);
		month = new Date(d.getFullYear(), d.getMonth(), 1);
		renderCal();
		renderSlots();
	}

	calEl.addEventListener('click', function (e) {
		var nav = e.target.closest('[data-nav]');
		if (nav && !nav.disabled) {
			month = new Date(month.getFullYear(), month.getMonth() + parseInt(nav.getAttribute('data-nav'), 10), 1);
			renderCal();
			return;
		}
		var day = e.target.closest('[data-date]');
		if (day && !day.disabled) {
			chooseDay(day.getAttribute('data-date'));
			renderCal();
			renderSlots();
		}
	});

	slotsEl.addEventListener('click', function (e) {
		var go = e.target.closest('[data-goto]');
		if (go) {
			pickDate(go.getAttribute('data-goto'));
			return;
		}
		if (e.target.closest('[data-all]')) {
			showAll = !showAll;
			renderSlots();
			return;
		}
		var t = e.target.closest('[data-time]');
		if (t) {
			selTime = t.getAttribute('data-time');
			renderSlots();
		}
	});

	function showForm(on) {
		stepPick.hidden = on;
		stepForm.hidden = !on;
		details.hidden = on;
		nextBtn.hidden = on;
		if (titleEl) {
			titleEl.textContent = titleEl.getAttribute(on ? 'data-t2' : 'data-t1');
		}
		if (introEl) {
			introEl.hidden = on;
		}
		window.scrollTo({ top: root.getBoundingClientRect().top + window.pageYOffset - 80, behavior: 'smooth' });
	}

	nextBtn.addEventListener('click', function () {
		if (!selTime) {
			return;
		}
		fDate.value = selDate;
		fTime.value = selTime;
		recap.textContent = parse(selDate).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) + ' à ' + selTime;
		showForm(true);
	});

	backLink.addEventListener('click', function (e) {
		if (!stepForm.hidden) {
			e.preventDefault();
			showForm(false);
		}
	});

	var more = root.querySelector('[data-more]');
	if (more) {
		more.addEventListener('click', function () {
			var box = root.querySelector('#afvf-bk-more');
			var open = box.hidden;
			box.hidden = !open;
			more.setAttribute('aria-expanded', open ? 'true' : 'false');
			more.querySelector('span').textContent = open ? 'Afficher moins' : 'Afficher plus';
			more.classList.toggle('is-open', open);
		});
	}

	if (tzEl && conf.tz) {
		var tzName = conf.tz.replace(/_/g, ' ');
		try {
			var parts = new Intl.DateTimeFormat('fr-FR', { timeZone: conf.tz, timeZoneName: 'long' }).formatToParts(new Date());
			parts.forEach(function (p) {
				if (p.type === 'timeZoneName') {
					tzName = p.value;
				}
			});
		} catch (err) { /* nom brut */ }
		tzEl.textContent = 'Fuseau horaire : ' + tzName + ' (' + (conf.utc || '').replace(/:00$/, '').replace(/([+-])0(\d)/, '$1$2') + ') ⌄';
	}

	var sumToggle = root.querySelector('[data-sumtoggle]');
	if (sumToggle) {
		sumToggle.addEventListener('click', function () {
			var box = root.querySelector('.afvf-bk__sumdetail');
			var open = box.hidden;
			box.hidden = !open;
			sumToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			sumToggle.querySelector('span').textContent = open ? 'Masquer les détails' : 'Afficher les détails';
			sumToggle.classList.toggle('is-open', open);
		});
	}

	if (avail[selDate] && avail[selDate].length) {
		selTime = avail[selDate][0];
	}
	renderCal();
	renderSlots();
})();
