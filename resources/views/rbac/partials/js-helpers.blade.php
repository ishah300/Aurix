<script>
(() => {
    window.Aurix = window.Aurix || {};

    if (typeof window.Aurix.escapeHtml !== 'function') {
        window.Aurix.escapeHtml = (value) => {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };
    }
})();
</script>
