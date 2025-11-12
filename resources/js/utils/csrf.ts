import axios from 'axios';

let csrfPromise: Promise<void> | null = null;

export const ensureCsrfCookie = async (): Promise<void> => {
    if (!csrfPromise) {
        csrfPromise = axios
            .get('/sanctum/csrf-cookie')
            .then(() => undefined)
            .catch((error) => {
                csrfPromise = null;
                throw error;
            });
    }

    return csrfPromise;
};
