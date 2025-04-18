let CookieUtils = {
  set(name, value, days = 365) {
    const expires = new Date(Date.now() + days * 86400000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/`;
  },

  get(name) {
    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
      const [key, value] = cookie.split('=').map(c => c.trim());
      acc[key] = value;
      return acc;
    }, {});
    return cookies[name] ? decodeURIComponent(cookies[name]) : null;
  }
};