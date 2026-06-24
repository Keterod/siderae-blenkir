/* global cy */

describe('Debug login', () => {
  it('logs network responses', () => {
    cy.intercept('GET', '/sanctum/csrf-cookie').as('csrf');
    cy.intercept('POST', '/login').as('login');
    cy.intercept('GET', '/api/me').as('me');

    cy.ensureLoggedOut();
    cy.getByTestId('login-email').type('admin@siderae.test');
    cy.getByTestId('login-password').type('password', { log: false });
    cy.getByTestId('login-submit').click();

    cy.wait('@csrf', { timeout: 30000 }).then((interception) => {
      cy.task('log', `csrf status: ${interception.response.statusCode}`);
      cy.task('log', `csrf url: ${interception.request.url}`);
      cy.task('log', `csrf set-cookie: ${String(interception.response.headers['set-cookie'])}`);
    });
    cy.wait('@login', { timeout: 30000 }).then((interception) => {
      cy.task('log', `login status: ${interception.response.statusCode}`);
      cy.task('log', `login url: ${interception.request.url}`);
      cy.task('log', `login request cookie: ${String(interception.request.headers.cookie)}`);
      cy.task('log', `login set-cookie: ${String(interception.response.headers['set-cookie'])}`);
      cy.task('log', `login body: ${JSON.stringify(interception.response.body).slice(0, 400)}`);
    });
    cy.getCookies().then((cookies) => {
      cy.task('log', `cookies after login: ${JSON.stringify(cookies)}`);
    });
    cy.wait('@me', { timeout: 30000 }).then((interception) => {
      cy.task('log', `me url: ${interception.request.url}`);
      cy.task('log', `me request cookie: ${String(interception.request.headers.cookie)}`);
      cy.task('log', `me status: ${interception.response.statusCode}`);
      cy.task('log', `me body: ${JSON.stringify(interception.response.body).slice(0, 400)}`);
    });

    cy.request({ method: 'GET', url: '/api/me', failOnStatusCode: false }).then((resp) => {
      cy.task('log', `cy.request me status: ${resp.status}`);
      cy.task('log', `cy.request me body: ${JSON.stringify(resp.body).slice(0, 400)}`);
    });

    cy.get('body', { timeout: 30000 }).then(($body) => {
      const hasWorkspace = $body.find('[data-testid="workspace-main"]').length > 0;
      const hasLogin = $body.find('[data-testid="login-screen"]').length > 0;
      cy.task('log', `hasWorkspace=${hasWorkspace} hasLogin=${hasLogin}`);
    });
  });
});
