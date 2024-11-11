import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { profileService } from '@/services/profile';
import { UserProfile, ProfileUpdateData } from '@/types/profile';
import { useToast } from '@/hooks/use-toast';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Progress } from '@/components/ui/progress';

const profileSchema = z.object({
    first_name: z.string().min(1, 'First name is required'),
    last_name: z.string().min(1, 'Last name is required'),
    phone: z.string().min(10, 'Please enter a valid phone number'),
    address: z.string().optional(),
    city: z.string().optional(),
    state: z.string().optional(),
    zip_code: z.string().optional(),
    housing_type: z.enum(['house', 'apartment', 'condo', 'other']).optional(),
    has_yard: z.boolean().optional(),
    other_pets: z.string().optional(),
    household_members: z.number().int().min(1).optional()
});

export default function ProfilePage() {
    const [profile, setProfile] = useState<UserProfile | null>(null);
    const [loading, setLoading] = useState(true);
    const { toast } = useToast();

    const form = useForm<ProfileUpdateData>({
        resolver: zodResolver(profileSchema)
    });

    useEffect(() => {
        loadProfile();
    }, []);

    const loadProfile = async () => {
        try {
            const data = await profileService.getProfile();
            setProfile(data);
            form.reset({
                first_name: data.first_name,
                last_name: data.last_name,
                phone: data.phone,
                address: data.address || undefined,
                city: data.city || undefined,
                state: data.state || undefined,
                zip_code: data.zip_code || undefined,
                housing_type: data.housing_type || undefined,
                has_yard: data.has_yard || undefined,
                other_pets: data.other_pets || undefined,
                household_members: data.household_members || undefined
            });
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: "Failed to load profile. Please try again.",
            });
        } finally {
            setLoading(false);
        }
    };

    const onSubmit = async (data: ProfileUpdateData) => {
        try {
            const updated = await profileService.updateProfile(data);
            setProfile(updated);
            toast({
                title: "Success",
                description: "Your profile has been updated.",
            });
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: error.response?.data?.error || "Failed to update profile",
            });
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    const completionPercentage = profile ? calculateProfileCompletion(profile) : 0;

    return (
        <div className="container max-w-2xl mx-auto p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Profile Settings</CardTitle>
                    <CardDescription>
                        Complete your profile to improve your adoption chances
                    </CardDescription>
                    <div className="mt-2">
                        <div className="flex justify-between text-sm mb-1">
                            <span>Profile Completion</span>
                            <span>{completionPercentage}%</span>
                        </div>
                        <Progress value={completionPercentage} />
                    </div>
                </CardHeader>

                <CardContent>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="first_name">First Name</Label>
                                <Input
                                    id="first_name"
                                    {...form.register('first_name')}
                                    aria-invalid={!!form.formState.errors.first_name}
                                />
                                {form.formState.errors.first_name && (
                                    <p className="text-sm text-destructive">
                                        {form.formState.errors.first_name.message}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="last_name">Last Name</Label>
                                <Input
                                    id="last_name"
                                    {...form.register('last_name')}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    {...form.register('phone')}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="housing_type">Housing Type</Label>
                                <Select
                                    id="housing_type"
                                    {...form.register('housing_type')}
                                >
                                    <option value="">Select...</option>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="condo">Condo</option>
                                    <option value="other">Other</option>
                                </Select>
                            </div>

                            {/* Add more form fields as needed */}
                        </div>

                        <Button type="submit" className="w-full">
                            Save Changes
                        </Button>
                    </form>
                </CardContent>

                {profile?.account_status === 'pending' && (
                    <CardFooter>
                        <Alert>
                            <AlertDescription>
                                Please verify your email address to complete your registration.
                                <Button variant="link" className="p-0 h-auto font-normal underline ml-1">
                                    Resend verification email
                                </Button>
                            </AlertDescription>
                        </Alert>
                    </CardFooter>
                )}
            </Card>
        </div>
    );
}

function calculateProfileCompletion(profile: UserProfile): number {
    const fields = [
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'housing_type',
        'has_yard',
        'other_pets',
        'household_members'
    ];

    const completedFields = fields.filter(field => 
        profile[field as keyof UserProfile] !== null && 
        profile[field as keyof UserProfile] !== undefined
    );

    return Math.round((completedFields.length / fields.length) * 100);
}
