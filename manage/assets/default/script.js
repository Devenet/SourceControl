
// Toogle new repository form
document.getElementById('box_add_repository').style.display = "none";
document.getElementById('add_repository').addEventListener('click', function(event) { var box = document.getElementById('box_add_repository'); if (box.style.display == "none") { box.style.display = "block"; } else { box.style.display = "none"; } event.preventDefault(); return false; });

// Toogle admin keys form
document.getElementById('box_admin_keys').style.display = "none";
document.getElementById('admin_keys').addEventListener('click', function(event) { var box = document.getElementById('box_admin_keys'); if (box.style.display == "none") { box.style.display = "block"; } else { box.style.display = "none"; } event.preventDefault(); return false; });

// Toogle details keys repository form
var details_keys_lines = document.getElementsByClassName("details-keys");
for (var i=0, length=details_keys_lines.length; i<length; i++) { details_keys_lines[i].style.display = 'none'; }
var details_keys_links = document.getElementsByClassName("details-keys-link");
for (var i=0, length=details_keys_links.length; i<length; i++) {
  details_keys_links[i].addEventListener('click', function(event) { var id = event.target.hash.substr(1); var box = document.getElementById(id); if (box.style.display == "none") { box.style.display = ""; } else { box.style.display = "none"; } event.preventDefault(); return false; });
}

// Load animation when updating repository
var update_links = document.getElementsByClassName("update-link");
for (var i=0, length=update_links.length; i<length; i++) {
  update_links[i].addEventListener('click', function(event) {
    var img = document.createElement('img');
    img.src = './assets/default/loader.gif';
    img.alt = 'Loading…';
    this.parentElement.style.display = 'none';
    this.parentElement.parentElement.appendChild(img);
  });
}

// Remove parent when close button clicked
var close_links = document.getElementsByClassName("close-link");
for (var i=0, length=close_links.length; i<length; i++) {
  close_links[i].addEventListener('click', function(event) { this.parentElement.remove(); event.preventDefault(); return false; });
}

// External links
var a = document.getElementsByTagName('a');
for (var i=0, length=a.length; i<length; i++) { if (a[i].getAttribute('rel') == 'external') { a[i].setAttribute('target', '_blank'); } }
