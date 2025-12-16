describe('Authentication', () => {
  const uniqueEmail = `test${Date.now()}@example.com`

  beforeEach(() => {
    cy.visit('/')
  })

  it('should register a new user', () => {
    cy.visit('/register')
    cy.get('input[name="firstname"]').type('John')
    cy.get('input[name="lastname"]').type('Doe')
    cy.get('input[name="email"]').type(uniqueEmail)
    cy.get('input[name="password"]').type('Password123!')
    cy.get('button[type="submit"]').click()

    cy.contains('Inscription réussie').should('be.visible')
  })

  it('should login with valid credentials', () => {
    cy.visit('/login')
    cy.get('input[name="email"]').type('admin@test.com')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()

    // Should redirect to home and show user email
    cy.url().should('eq', Cypress.config().baseUrl + '/')
    cy.contains('admin@test.com').should('be.visible')
  })

  it('should show error with invalid credentials', () => {
    cy.visit('/login')
    cy.get('input[name="email"]').type('invalid@example.com')
    cy.get('input[name="password"]').type('wrongpassword')
    cy.get('button[type="submit"]').click()

    cy.contains('Login failed').should('be.visible')
  })

  it('should logout successfully', () => {
    // Login first
    cy.visit('/login')
    cy.get('input[name="email"]').type('admin@test.com')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    cy.wait(1000)

    // Then logout
    cy.contains('Déconnexion').click()
    cy.contains('Connexion').should('be.visible')
  })
})
