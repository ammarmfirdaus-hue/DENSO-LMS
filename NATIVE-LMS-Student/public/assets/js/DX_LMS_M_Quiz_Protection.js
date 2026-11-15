(function () {
  'use strict';
  var root = document.documentElement;
  var protectedPage = document.body.classList.contains('dls-quiz-protected');
  if (!protectedPage) return;

  function block(event) {
    event.preventDefault();
    event.stopPropagation();
  }

  ['contextmenu', 'copy', 'cut', 'dragstart', 'selectstart'].forEach(function (name) {
    document.addEventListener(name, block, true);
  });

  document.addEventListener('keydown', function (event) {
    var key = String(event.key || '').toLowerCase();
    var blocked = key === 'f12'
      || (event.ctrlKey && event.shiftKey && ['i', 'j', 'c'].includes(key))
      || (event.ctrlKey && ['u', 's', 'p'].includes(key));
    if (blocked) block(event);
  }, true);

  document.addEventListener('visibilitychange', function () {
    var away = document.visibilityState === 'hidden';
    root.classList.toggle('dls-quiz-away', away);
    if (!away) window.setTimeout(function () { root.classList.remove('dls-quiz-away'); }, 250);
  });

  // Tidak menjamin anti-screen-capture, tetapi mencegah hasil quiz terbaca saat
  // pengguna berpindah tab/aplikasi dan mengurangi kebocoran kasual.
  window.addEventListener('beforeprint', function () { root.classList.add('dls-quiz-away'); });
  window.addEventListener('afterprint', function () { root.classList.remove('dls-quiz-away'); });
}());
