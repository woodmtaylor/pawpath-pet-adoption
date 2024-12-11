export interface UserProfile {
    profile_id: number;
    user_id: number;
    first_name: string;
    last_name: string;
    phone: string;
    address: string | null;
    city: string | null;
    state: string | null;
    zip_code: string | null;
    housing_type: 'house' | 'apartment' | 'condo' | 'other' | null;
    has_yard: boolean | null;
    other_pets: string | null;
    household_members: number | null;
    email: string;
    username: string;
    role: 'adopter' | 'shelter_staff' | 'admin';
    account_status: 'pending' | 'active' | 'suspended';
    profile_image: string | null;
}

export interface ProfileUpdateData {
    first_name: string;
    last_name: string;
    phone: string;
    address?: string;
    city?: string;
    state?: string;
    zip_code?: string;
    housing_type?: 'house' | 'apartment' | 'condo' | 'other';
    has_yard?: boolean;
    other_pets?: string;
    household_members?: number;
}
