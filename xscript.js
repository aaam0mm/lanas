// Helper Functions to generate random values
function getRandomFromArray(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Random Arabic names, usernames, and emails
const randomNames = ["محمد علي", "أحمد يوسف", "سارة إبراهيم", "فاطمة حسن", "علي عبد الله"];
const randomUsernames = ["mohamed_ali", "ahmed_yousef", "sara_ibrahim", "fatima_hasan", "ali_abdullah"];
const randomEmails = ["sahamza2@gmail.com", "bellalsalaheddine394@gmail.com", "billalsalah199@gmail.com"];
const randomCountries = ["iq", "et", "az", "ng", "nz", "yu", 'dz'];
const randomGenders = ["male", "female"];
const randomPasswords = ["salah333"];

// Random birthdates
const randomBirthDay = getRandomInt(1, 31);
const randomBirthMonth = getRandomInt(1, 12);
const randomBirthYear = getRandomInt(1940, 2024);

// Random form data
const formData = {
  user_name: getRandomFromArray(randomNames),
  username: getRandomFromArray(randomUsernames),
  user_email: getRandomFromArray(randomEmails),
  user_gender: getRandomFromArray(randomGenders),
  user_birth_day: randomBirthDay,
  user_birth_month: randomBirthMonth,
  user_birth_year: randomBirthYear,
  user_country: getRandomFromArray(randomCountries),
  user_pwd: getRandomFromArray(randomPasswords),
  user_re_pwd: getRandomFromArray(randomPasswords)
};

// Fill form fields
document.querySelector('input[name="user_name"]').value = formData.user_name;
document.querySelector('input[name="username"]').value = formData.username;
document.querySelector('input[name="user_email"]').value = formData.user_email;
document.querySelector(`select[name="user_gender"] option[value="${formData.user_gender}"]`).selected = true;
document.querySelector('select[name="user_birth_day"]').value = formData.user_birth_day;
document.querySelector('select[name="user_birth_month"]').value = formData.user_birth_month;
document.querySelector('select[name="user_birth_year"]').value = formData.user_birth_year;
document.querySelector('select[name="user_country"]').value = formData.user_country;
document.querySelector('input[name="user_pwd"]').value = formData.user_pwd;
document.querySelector('input[name="user_re_pwd"]').value = formData.user_re_pwd;

// Console output for verification (Optional)
console.log("Form filled with random data:", formData);








// auto fill fetch posts

(function() {
  // Function to generate a random string
  function randomString(length) {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      let result = '';
      for (let i = 0; i < length; i++) {
          result += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return result;
  }

  // Function to generate a random number within a range
  function randomNumber(min, max) {
      return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  // Fill the "language" dropdown
  const languageSelect = document.getElementById('language');
  if (languageSelect) {
      const options = languageSelect.getElementsByTagName('option');
      if (options.length > 1) {
          languageSelect.selectedIndex = randomNumber(1, options.length - 1); // Ignore the disabled option
      }
  }

  // Fill the "type" dropdown
  const typeSelect = document.getElementById('type');
  if (typeSelect) {
      const options = typeSelect.getElementsByTagName('option');
      if (options.length > 1) {
          typeSelect.selectedIndex = randomNumber(1, options.length - 1); // Ignore the disabled option
      }
  }

  // Fill the "department" dropdown
  const departmentSelect = document.getElementById('department');
  if (departmentSelect) {
      const options = departmentSelect.getElementsByTagName('option');
      if (options.length > 1) {
          departmentSelect.selectedIndex = randomNumber(1, options.length - 1); // Ignore the disabled option
      }
  }

  // Always set the "account" dropdown to 1
  const accountSelect = document.getElementById('account');
  if (accountSelect) {
      accountSelect.value = '1';
  }

  // Fill the "url" input with random data
  const urlInput = document.getElementById('url');
  if (urlInput) {
      urlInput.value = `https://example.com/${randomString(10)}`;
  }

  // Randomly check or uncheck the "auto_share" checkbox
  const autoShareCheckbox = document.getElementById('auto_share');
  if (autoShareCheckbox) {
      autoShareCheckbox.checked = Math.random() >= 0.5;
  }

  // Randomly check or uncheck the "show_pic" checkbox
  const showPicCheckbox = document.getElementById('show_pic');
  if (showPicCheckbox) {
      showPicCheckbox.checked = Math.random() >= 0.5;
  }

  // Fill the "count" input with a random number between 1 and 100
  const countInput = document.getElementById('count');
  if (countInput) {
      countInput.value = randomNumber(1, 100);
  }

  // Fill the "source1" input with random text
  const source1Input = document.getElementById('source1');
  if (source1Input) {
      source1Input.value = `${randomString(8)}:https://www.exemple.com`;
  }

  // Fill the "source2" input with random text
  const source2Input = document.getElementById('source2');
  if (source2Input) {
      source2Input.value = `${randomString(8)}:https://www.exemple.com`;
  }

  // Simulate form submission if required (optional)
  // document.getElementById('save_data_form').submit();

  console.log("Form filled with random data!");
})();



// mmysql -u u794773365_nas -A u794773365_nas -p 