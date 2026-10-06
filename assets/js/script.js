(function () {
  var $ = function (id) { return document.getElementById(id); };
  document.querySelectorAll('input[name="schedule_id"]').forEach(function (r) {
    r.addEventListener('change', function () {
      document.querySelectorAll('.slot').forEach(function (s) { s.classList.remove('sel'); });
      var l = r.closest('.slot'); if (l) l.classList.add('sel');
      if ($('sField')) $('sField').textContent = r.dataset.field;
      if ($('sTime')) $('sTime').textContent = r.dataset.time;
      if ($('sPrice')) $('sPrice').textContent = r.dataset.price;
      if ($('bookBtn')) $('bookBtn').disabled = false;
    });
  });
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });
  document.querySelectorAll('table').forEach(function (t) {
    var heads = [];
    t.querySelectorAll('tr').forEach(function (tr) {
      var th = tr.querySelectorAll('th');
      if (th.length) { heads = Array.prototype.map.call(th, function (h) { return h.textContent.trim(); }); tr.classList.add('hr'); return; }
      tr.querySelectorAll('td').forEach(function (td, i) { if (heads[i]) td.setAttribute('data-label', heads[i]); });
    });
  });
})();
