</div><!-- end main-wrapper -->

<div id="toast" class="toast"></div>

<script>
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show ' + type;
    setTimeout(() => t.className = 'toast', 3000);
}
// Auto show toast from URL param
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success')) showToast(decodeURIComponent(urlParams.get('success')), 'success');
if (urlParams.get('error')) showToast(decodeURIComponent(urlParams.get('error')), 'error');
</script>
</body>
</html>
