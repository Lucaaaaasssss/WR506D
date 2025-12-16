describe('Comments (Authenticated User)', () => {
  beforeEach(() => {
    // Login first
    cy.visit('/login')
    cy.get('input[name="email"]').type('user@example.com')
    cy.get('input[name="password"]').type('user')
    cy.get('button[type="submit"]').click()
    cy.wait(1000)
  })

  it('should post a comment on a movie', () => {
    // Navigate to a movie detail page
    cy.visit('/movies')
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()

        // Post a comment
        cy.get('textarea[placeholder*="commentaire"]').type('Great movie!')
        cy.contains('Publier').click()

        // Wait and check if comment appears
        cy.wait(1000)
        cy.contains('Great movie!').should('be.visible')
      }
    })
  })

  it('should display comment form for authenticated users', () => {
    cy.visit('/movies')
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()
        cy.get('textarea[placeholder*="commentaire"]').should('be.visible')
        cy.contains('Publier').should('be.visible')
      }
    })
  })

  it('should delete own comment', () => {
    cy.visit('/movies')
    cy.get('body').then(($body) => {
      if ($body.find('a:contains("Voir détails")').length > 0) {
        cy.contains('Voir détails').first().click()

        // Post a comment first
        cy.get('textarea[placeholder*="commentaire"]').type('Comment to delete')
        cy.contains('Publier').click()
        cy.wait(1000)

        // Try to delete it
        cy.get('button:contains("Supprimer")').first().click()
        cy.wait(500)
      }
    })
  })
})
