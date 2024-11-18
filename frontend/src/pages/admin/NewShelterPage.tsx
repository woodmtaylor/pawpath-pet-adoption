import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { useToast } from '@/hooks/use-toast';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/axios';

interface ShelterFormData {
    name: string;
    address: string;
    phone: string;
    email: string;
    is_no_kill: boolean;
}

export default function NewShelterPage() {
    const [formData, setFormData] = useState<ShelterFormData>({
        name: '',
        address: '',
        phone: '',
        email: '',
        is_no_kill: false
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { toast } = useToast();
    const navigate = useNavigate();
    const { user } = useAuth();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        try {
            setIsSubmitting(true);
            const response = await api.post('/admin/shelters', formData);
            
            if (response.data.success) {
                toast({
                    title: "Success",
                    description: "Shelter created successfully"
                });
                navigate('/admin/shelters');
            } else {
                throw new Error(response.data.error || 'Failed to create shelter');
            }
        } catch (error: any) {
            toast({
                variant: "destructive",
                title: "Error",
                description: error.response?.data?.error || "Failed to create shelter"
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    return (
        <div className="container max-w-2xl mx-auto p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Add New Shelter</CardTitle>
                    <CardDescription>
                        Create a new animal shelter in the system
                    </CardDescription>
                </CardHeader>

                <form onSubmit={handleSubmit}>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Shelter Name</Label>
                            <Input
                                id="name"
                                name="name"
                                placeholder="Enter shelter name"
                                required
                                value={formData.name}
                                onChange={handleChange}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="address">Address</Label>
                            <Input
                                id="address"
                                name="address"
                                placeholder="Enter full address"
                                required
                                value={formData.address}
                                onChange={handleChange}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="phone">Phone Number</Label>
                            <Input
                                id="phone"
                                name="phone"
                                placeholder="Enter phone number"
                                required
                                value={formData.phone}
                                onChange={handleChange}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="Enter email address"
                                required
                                value={formData.email}
                                onChange={handleChange}
                            />
                        </div>

                        <div className="flex items-center space-x-2">
                            <Checkbox
                                id="is_no_kill"
                                name="is_no_kill"
                                checked={formData.is_no_kill}
                                onCheckedChange={(checked) => 
                                    setFormData(prev => ({ ...prev, is_no_kill: checked as boolean }))
                                }
                            />
                            <Label htmlFor="is_no_kill">This is a no-kill shelter</Label>
                        </div>
                    </CardContent>

                    <CardFooter className="flex justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => navigate('/admin/shelters')}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Creating...' : 'Create Shelter'}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    );
}
