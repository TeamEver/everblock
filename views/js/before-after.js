document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.everblock-before-after').forEach(function (el) {
    var range = el.querySelector('.eba-range');
    var after = el.querySelector('.eba-after');
    if (range && after) {
      range.addEventListener('input', function () {
        after.style.width = range.value + '%';
      });
    }
  });
});
