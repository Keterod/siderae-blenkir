async function main() {
  const csrf = await fetch('http://localhost:8000/sanctum/csrf-cookie', {
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });
  console.log('csrf status', csrf.status);
  const setCookie = csrf.headers.get('set-cookie');
  console.log('set-cookie', setCookie);
  let xsrfToken = '';
  if (setCookie) {
    const m = setCookie.match(/XSRF-TOKEN=([^;]+)/);
    if (m) xsrfToken = decodeURIComponent(m[1]);
  }
  console.log('xsrf token prefix', xsrfToken.slice(0, 40));

  const login = await fetch('http://localhost:8000/login', {
    method: 'POST',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': xsrfToken,
    },
    body: JSON.stringify({ email: 'admin@siderae.test', password: 'password' }),
  });
  console.log('login status', login.status);
  const loginText = await login.text();
  console.log('login body', loginText.slice(0, 300));

  const me = await fetch('http://localhost:8000/api/me', {
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });
  console.log('me status', me.status);
  const meText = await me.text();
  console.log('me body', meText.slice(0, 300));
}
main().catch((e) => console.error(e));
