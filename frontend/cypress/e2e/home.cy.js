describe('Home Page', () => {
  it('should display the home page correctly', () => {
    cy.visit('/')
    cy.contains('Bomboclaat').should('be.visible')
    cy.contains('Explorer les films').should('be.visible')
  })

  it('should navigate to movies list', () => {
    cy.visit('/')
    cy.contains('Explorer les films').click()
    cy.url().should('include', '/movies')
  })

  it('should display features section', () => {
    cy.visit('/')
    cy.contains('Catalogue').should('be.visible')
    cy.contains('Communauté').should('be.visible')
    cy.contains('Recherche').should('be.visible')
  })
})
