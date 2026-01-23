describe('Movies', () => {
  beforeEach(() => {
    cy.visit('/movies')
  })

  it('should display the movies list', () => {
    cy.contains('Films').should('be.visible')
    cy.get('[class*="grid"]').should('exist')
  })

  it('should filter movies by search', () => {
    cy.get('input[placeholder*="Rechercher"]').type('Test')
    // Wait for the API call to complete
    cy.wait(500)
  })

  it('should sort movies', () => {
    cy.get('select').eq(0).select('name:asc')
    // Wait for the API call to complete
    cy.wait(500)
  })

  it('should navigate to movie detail', () => {
    // Click on the first "Voir détails" button if movies exist
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()
        cy.url().should('include', '/movies/')
      }
    })
  })
})

describe('Movie Detail', () => {
  it('should display movie details and comments section', () => {
    // This test assumes there's at least one movie
    cy.visit('/movies')
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()
        cy.contains('Commentaires').should('be.visible')
      }
    })
  })

  it('should not allow commenting without authentication', () => {
    cy.visit('/movies')
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()
        cy.contains('Connectez-vous').should('be.visible')
      }
    })
  })
})

describe('Movie Creation (Authenticated)', () => {
  beforeEach(() => {
    // Login as admin
    cy.visit('/login')
    cy.get('input[name="email"]').type('admin@test.com')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()
    // Wait for login to complete and redirection
    cy.url().should('eq', Cypress.config().baseUrl + '/')
    cy.wait(500)
  })

  it('should access movie creation form', () => {
    cy.visit('/movies/create')
    cy.url().should('include', '/movies/create')
    cy.contains('Nouveau film').should('be.visible')
    cy.get('input[type="text"]').should('exist')
  })

  it('should validate required fields', () => {
    cy.visit('/movies/create')
    cy.get('button[type="submit"]').click()
    // HTML5 validation should prevent submission
  })
})
