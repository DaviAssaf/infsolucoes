function mostrarSenha() {
    var senha = document.getElementById("password");
    var olho = document.getElementById("togglePassword");

    if (!senha || !olho) return;
    if (senha.type === "password") {
        senha.type = "text";
        olho.classList.remove("fa-eye");
        olho.classList.add("fa-eye-slash");
    } else {
        senha.type = "password";
        olho.classList.remove("fa-eye-slash");
        olho.classList.add("fa-eye");
    }
}
