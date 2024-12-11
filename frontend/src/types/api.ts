export interface User {
    user_id: number;
    username: string;
    email: string;
    role: 'admin' | 'shelter_staff' | 'adopter';
    profile_image?: string;
}

export interface PetImage {
    image_id: number;
    url: string;
    is_primary: boolean;
    created_at: string;
}

export interface PetTrait {
    trait_id: number;
    trait_name: string;
    category: string;
}

export interface Pet {
    pet_id: number;
    name: string;
    species: string;
    breed: string;
    age: number;
    gender: string;
    description: string;
    shelter_name: string;
    traits: {
        [category: string]: string[];
    };
    images?: PetImage[];
}

export interface ApiResponse<T> {
    success: boolean;
    data?: T;
    error?: string;
}

export interface PaginatedResponse<T> extends ApiResponse<T> {
    data: {
        items: T[];
        total: number;
        page: number;
        perPage: number;
    };
}
