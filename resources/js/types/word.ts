export type Word = {
    id: number;
    user_id: number;
    word: string;
    pronunciation: string;
    part_of_speech: string;
    meaning: string;
    example_sentence: string;
    difficulty: 'easy' | 'medium' | 'hard';
    image?: string | null;
};
