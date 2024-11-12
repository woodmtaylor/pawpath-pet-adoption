import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { profileService } from '@/services/profile';
import { UserProfile, ProfileUpdateData } from '@/types/profile';
import { useToast } from '@/hooks/use-toast';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useAuth } from '@/contexts/AuthContext';

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
    const { user } = useAuth();
    
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
                description: "Failed to load profile",
            });
        } finally {
            setLoading(false);
        }
    };

    const renderRoleSpecificContent = () => {
        switch (user?.role) {
            case 'shelter_staff':
                return (
                    <TabsContent value="shelter">
                        <Card>
                            <CardHeader>
                                <CardTitle>Shelter Management</CardTitle>
                                <CardDescription>
                                    Manage your shelter's information and pets
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {/* Shelter staff specific content */}
                            </CardContent>
                        </Card>
                    </TabsContent>
                );
            case 'admin':
                return (
                    <TabsContent value="admin">
                        <Card>
                            <CardHeader>
                                <CardTitle>Admin Dashboard</CardTitle>
                                <CardDescription>
                                    System administration tools
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {/* Admin specific content */}
                            </CardContent>
                        </Card>
                    </TabsContent>
                );
            default:
                return null;
        }
    };

    if (loading) {
        return <div>Loading...</div>;
    }

    return (
        <div className="container max-w-4xl mx-auto p-4">
            <Tabs defaultValue="profile">
                <TabsList>
                    <TabsTrigger value="profile">Profile</TabsTrigger>
                    {user?.role === 'shelter_staff' && (
                        <TabsTrigger value="shelter">Shelter Management</TabsTrigger>
                    )}
                    {user?.role === 'admin' && (
                        <TabsTrigger value="admin">Admin Dashboard</TabsTrigger>
                    )}
                </TabsList>

                <TabsContent value="profile">
                    <Card>
                        <CardHeader>
                            <CardTitle>Profile Settings</CardTitle>
                            <CardDescription>
                                Manage your personal information
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {/* Existing profile form content */}
                        </CardContent>
                    </Card>
                </TabsContent>

                {renderRoleSpecificContent()}
            </Tabs>
        </div>
    );
}
