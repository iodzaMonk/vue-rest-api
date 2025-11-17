describe('Word search and autocomplete', () => {
    beforeEach(() => {
        cy.fixture('words').then((words) => {
            cy.intercept('GET', '/api/words', words).as('getWords');
        });

        cy.intercept('GET', '/api/words/autocomplete*', (req) => {
            const query = (req.query.query as string) || '';
            const matches = ['Apple', 'Banana'].filter((item) => item.toLowerCase().startsWith(query.toLowerCase()));
            req.reply({ data: matches });
        }).as('autocomplete');

        cy.visit('/', {
            onBeforeLoad(win) {
                win.localStorage.setItem('auth_token', 'fake-token');
            },
        });

        cy.wait('@getWords');
    });

    it('shows the initial word list', () => {
        cy.get('article').should('have.length', 40);
        cy.contains('Apple');
        cy.contains('Banana');
    });

    it('filters the list based on autocomplete selection', () => {
        cy.get('input[placeholder="Search words"]').type('Ap');
        cy.wait('@autocomplete');
        cy.contains('h2', 'Apple').click();
        cy.get('article').should('have.length', 5);
        cy.contains('Apple');
        cy.contains('Banana').should('not.exist');
    });
});
