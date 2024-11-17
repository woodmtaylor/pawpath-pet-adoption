import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useToast } from '@/hooks/use-toast';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import api from '@/lib/axios';

const adoptionFormSchema = z.object({
  reason: z.string().min(50, 'Please provide a detailed reason for wanting to adopt this pet'),
  experience: z.string().min(20, 'Please describe your pet care experience'),
  living_situation: z.string().min(20, 'Please describe your living situation'),
  has_other_pets: z.boolean().optional(),
  other_pets_details: z.string().optional(),
  daily_schedule: z.string().min(20, 'Please describe your daily schedule'),
  veterinarian: z.string().optional(),
});

type AdoptionFormData = z.infer<typeof adoptionFormSchema>;

export default function AdoptionFormPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const form = useForm<AdoptionFormData>({
    resolver: zodResolver(adoptionFormSchema),
    defaultValues: {
      reason: '',
      experience: '',
      living_situation: '',
      has_other_pets: false,
      other_pets_details: '',
      daily_schedule: '',
      veterinarian: '',
    },
  });

  const onSubmit = async (data: AdoptionFormData) => {
    try {
      setIsSubmitting(true);
      const response = await api.post(`/adoptions`, {
        pet_id: id,
        ...data,
      });

      toast({
        title: "Application Submitted",
        description: "Your adoption application has been received. We'll be in touch soon!",
      });

      navigate('/profile/applications');
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: "Error",
        description: error.response?.data?.error || "Failed to submit application",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="container max-w-2xl mx-auto p-4">
      <Card>
        <CardHeader>
          <CardTitle>Adoption Application</CardTitle>
          <CardDescription>
            Please fill out this form to begin the adoption process
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              <FormField
                control={form.control}
                name="reason"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Why do you want to adopt this pet?</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Tell us why you think you'd be a great match..."
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="experience"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Pet Care Experience</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Describe your experience with pets..."
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="living_situation"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Living Situation</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Describe your home and living environment..."
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="daily_schedule"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Daily Schedule</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Describe your typical daily schedule..."
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="veterinarian"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Veterinarian (Optional)</FormLabel>
                    <FormControl>
                      <Input
                        placeholder="Name and contact of your veterinarian..."
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="flex gap-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => navigate(-1)}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={isSubmitting}
                  className="flex-1"
                >
                  {isSubmitting ? 'Submitting...' : 'Submit Application'}
                </Button>
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  );
}
