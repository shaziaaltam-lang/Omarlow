<div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" onclick="setLanguage('ar')">
        <img src="{{ asset('assets/images/flags/ar.png') }}" alt="العربية" height="20"> العربية
    </button>
    <button class="btn btn-sm btn-outline-secondary" onclick="setLanguage('en')">
        <img src="{{ asset('assets/images/flags/en.png') }}" alt="English" height="20"> English
    </button>
</div>

<script>
function setLanguage(lang) {
    localStorage.setItem('language', lang);
    window.location.reload();
}
</script>
