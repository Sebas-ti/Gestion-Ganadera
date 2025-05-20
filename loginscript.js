
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(event) {
      let valid = true;

      const userInput = document.getElementById('usernameOrEmail');
      const userError = document.getElementById('userError');
      if (userInput.value.trim() === '') {
        userError.textContent = 'Este campo es obligatorio.';
        valid = false;
      } else {
        userError.textContent = '';
      }

      const passInput = document.getElementById('password');
      const passError = document.getElementById('passError');
      if (passInput.value.length < 6) {
        passError.textContent = 'La contraseña debe tener al menos 6 caracteres.';
        valid = false;
      } else {
        passError.textContent = '';
      }

      if (!valid) event.preventDefault();
    });
