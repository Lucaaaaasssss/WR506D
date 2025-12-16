describe('Home Page', () => {
  it('should display the home page correctly', () => {
    cy.visit('/')
    cy.contains('Bienvenue sur MovieCMS').should('be.visible')
    cy.contains('Découvrir les films').should('be.visible')
  })

  it('should navigate to movies list', () => {
    cy.visit('/')
    cy.contains('Découvrir les films').click()
    cy.url().should('include', '/movies')
  })

  it('should display test accounts info when not authenticated', () => {
    cy.visit('/')
    cy.contains('Comptes de test disponibles').should('be.visible')
    cy.contains('admin@test.com').should('be.visible')
    cy.contains('user@test.com').should('be.visible')
  })
})
