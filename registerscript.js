 
    // Validación simple en el cliente
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', function(event) {
      let valid = true;

      // Username
      const username = document.getElementById('username');
      const usernameError = document.getElementById('usernameError');
      if (username.value.trim().length < 3) {
        usernameError.textContent = 'El nombre de usuario debe tener al menos 3 caracteres.';
        valid = false;
      } else {
        usernameError.textContent = '';
      }

      // Email
      const email = document.getElementById('email');
      const emailError = document.getElementById('emailError');
      if (!email.value.includes('@')) {
        emailError.textContent = 'Por favor ingresa un correo válido.';
        valid = false;
      } else {
        emailError.textContent = '';
      }

      // Password
      const password = document.getElementById('password');
      const passwordError = document.getElementById('passwordError');
      if (password.value.length < 6) {
        passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres.';
        valid = false;
      } else {
        passwordError.textContent = '';
      }

      // Confirm Password
      const confirmPassword = document.getElementById('confirmPassword');
      const confirmPasswordError = document.getElementById('confirmPasswordError');
      if (confirmPassword.value !== password.value) {
        confirmPasswordError.textContent = 'Las contraseñas no coinciden.';
        valid = false;
      } else {
        confirmPasswordError.textContent = '';
      }

      if (!valid) {
        event.preventDefault();
      }
    });
