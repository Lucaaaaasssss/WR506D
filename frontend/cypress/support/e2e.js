// Cypress support file

// Custom commands
Cypress.Commands.add('login', (email, password) => {
  cy.visit('/login')
  cy.get('input[name="email"]').type(email)
  cy.get('input[name="password"]').type(password)
  cy.get('button[type="submit"]').click()
})

Cypress.Commands.add('register', (userData) => {
  cy.visit('/register')
  if (userData.firstname) {
    cy.get('input[name="firstname"]').type(userData.firstname)
  }
  if (userData.lastname) {
    cy.get('input[name="lastname"]').type(userData.lastname)
  }
  cy.get('input[name="email"]').type(userData.email)
  cy.get('input[name="password"]').type(userData.password)
  cy.get('button[type="submit"]').click()
})
