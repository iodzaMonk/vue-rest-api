describe('Word deletion flow', () => {
    beforeEach(() => {
        cy.fixture('words').then((words) => {
            cy.intercept('GET', '/api/words', words).as('getWords');
        });

        cy.visit('/', {
            onBeforeLoad(win) {
                win.localStorage.setItem('auth_token', 'fake-token');
            },
        });

        cy.wait('@getWords');
    });

    it('opens delete confirmation and removes a word', () => {
        cy.intercept('DELETE', /\/api\/words\/\d+/, {}).as('deleteWord');

        // Ensure initial list length
        cy.get('article').should('have.length.at.least', 2);

        // Click delete on the first card
        cy.get('article')
            .first()
            .within(() => {
                cy.contains('Delete').click();
            });

        // Confirm dialog appears
        cy.contains('Delete word').should('exist');
        cy.contains('Delete').click();

        cy.wait('@deleteWord');

        // List should shrink by one
        cy.get('article').should('have.length.lessThan', 40);
    });
});

it('Update', function () {
    cy.visit('http://127.0.0.1:8000/');
    cy.get('#app button.rounded.text-white').click();
    cy.get('#app input[placeholder="John Doe"]').click();
    cy.get('#app input[placeholder="John Doe"]').type('test');
    cy.get('#app input[type="email"]').type('test@admin.com');
    cy.get('#app div.bg-slate-900\\/50').click();
    cy.get('#app button.rounded.text-white').click();
    cy.get('#app input[placeholder="John Doe"]').click();
    cy.get('#app input[placeholder="John Doe"]').type('test@admin.com');
    cy.get('#app input[type="email"]').type('test@admin.com');
    cy.get('#app input[placeholder="John Doe"]').click();
    cy.get('#app input[placeholder="John Doe"]').clear();
    cy.get('#app input[placeholder="John Doe"]').type('test');
    cy.get('#app input[placeholder="Password"]').click();
    cy.get('#app input[placeholder="Password"]').type('123');
    cy.get('#app input[placeholder="Confirm password"]').click();
    cy.get('#app input[placeholder="Confirm password"]').type('123');
    cy.get('#app button.disabled\\:bg-slate-300').click();
    cy.get('#app input[type="email"]').click();
    cy.get('#app input[type="email"]').type('test@admin.com');
    cy.get('#app input[type="password"]').type('123');
    cy.get('#app button.disabled\\:bg-slate-300').click();
    cy.get('#app button.uppercase').click();
    cy.get('#app input[placeholder="serendipity"]').click();
    cy.get('#app input[placeholder="serendipity"]').type('123');
    cy.get('#app input[placeholder="seh·ruhn·di·puh·tee"]').click();
    cy.get('#app input[placeholder="seh·ruhn·di·puh·tee"]').type('123');
    cy.get('#app input[placeholder="noun"]').click();
    cy.get('#app input[placeholder="noun"]').type('123');
    cy.get('#app label.transition').click();
    cy.get('#app input.hidden').click();
    cy.get('#app textarea[placeholder="An unexpected pleasant discovery."]').click();
    cy.get('#app textarea[placeholder="An unexpected pleasant discovery."]').click();
    cy.get('#app textarea[placeholder="An unexpected pleasant discovery."]').type('123');
    cy.get('#app textarea[placeholder="Discovering her favorite author lived nearby was pure serendipity."]').click();
    cy.get('#app textarea[placeholder="Discovering her favorite author lived nearby was pure serendipity."]').type('123');
    cy.get('#app section.border').click();
    cy.get('#app button.disabled\\:bg-slate-300').click();
    cy.get('#app div.mt-auto button.text-white').click();
    cy.get('#app section.border').click();
    cy.get('#app input[placeholder="serendipity"]').clear();
    cy.get('#app input[placeholder="serendipity"]').type('elephant');
    cy.get('#app button.disabled\\:bg-slate-300').click();
    cy.get('#app div.grid').click();
});
