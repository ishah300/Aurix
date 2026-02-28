@include('aurix::rbac.partials.favicon-loader')
@include($layoutView)

<script>
(() => {
    // When viewing the Aurix RBAC UI inside the host app layout,
    // ensure any auth-page background styles previously applied
    // by the auth theme loader to the shared `.min-h-screen`
    // shell are cleared, so RBAC screens keep their own neutral
    // background.
    const resetShellBackground = () => {
        const shell = document.querySelector('.min-h-screen');
        if (!shell) return;

        shell.style.removeProperty('background-color');
        shell.style.removeProperty('background-image');
        shell.style.removeProperty('background-size');
        shell.style.removeProperty('background-position');
        shell.style.removeProperty('background-repeat');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', resetShellBackground, { once: true });
    } else {
        resetShellBackground();
    }
})();
</script>
