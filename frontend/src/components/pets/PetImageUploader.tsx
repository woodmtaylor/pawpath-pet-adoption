import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useToast } from '@/hooks/use-toast';
import { Loader2, X, Image as ImageIcon, Star } from 'lucide-react';
import api from '@/lib/axios';

interface PetImage {
  image_id: number;
  url: string;
  is_primary: boolean;
}

interface PetImageUploaderProps {
  petId: number;
  existingImages?: PetImage[];
  onImagesUpdated: (images: PetImage[]) => void;
}

export function PetImageUploader({ petId, existingImages = [], onImagesUpdated }: PetImageUploaderProps) {
  const [uploading, setUploading] = useState(false);
  const [images, setImages] = useState<PetImage[]>(existingImages);
  const { toast } = useToast();

  const handleUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    if (!event.target.files?.length) return;

    try {
      setUploading(true);
      const formData = new FormData();
      Array.from(event.target.files).forEach((file) => {
        formData.append('images[]', file);
      });

      const response = await api.post(`/pets/${petId}/images`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      if (response.data.success) {
        const newImages = [...images, ...response.data.data];
        setImages(newImages);
        onImagesUpdated(newImages);
        toast({
          title: "Success",
          description: "Images uploaded successfully"
        });
      }
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Error",
        description: "Failed to upload images"
      });
    } finally {
      setUploading(false);
    }
  };

  const handleDelete = async (imageId: number) => {
    try {
      const response = await api.delete(`/pets/${petId}/images/${imageId}`);
      if (response.data.success) {
        const updatedImages = images.filter(img => img.image_id !== imageId);
        setImages(updatedImages);
        onImagesUpdated(updatedImages);
        toast({
          title: "Success",
          description: "Image deleted successfully"
        });
      }
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Error",
        description: "Failed to delete image"
      });
    }
  };

  const setPrimary = async (imageId: number) => {
    try {
      const response = await api.put(`/pets/${petId}/images/${imageId}/primary`);
      if (response.data.success) {
        const updatedImages = images.map(img => ({
          ...img,
          is_primary: img.image_id === imageId
        }));
        setImages(updatedImages);
        onImagesUpdated(updatedImages);
        toast({
          title: "Success",
          description: "Primary image updated"
        });
      }
    } catch (error) {
      toast({
        variant: "destructive",
        title: "Error",
        description: "Failed to update primary image"
      });
    }
  };

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {images.map((image) => (
          <Card key={image.image_id} className="relative group">
            <CardContent className="p-2">
              <img
                src={image.url}
                alt="Pet"
                className="w-full aspect-square object-cover rounded-md"
              />
              <div className="absolute top-4 right-4 space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                {!image.is_primary && (
                  <Button
                    size="icon"
                    variant="secondary"
                    onClick={() => setPrimary(image.image_id)}
                  >
                    <Star className="h-4 w-4" />
                  </Button>
                )}
                <Button
                  size="icon"
                  variant="destructive"
                  onClick={() => handleDelete(image.image_id)}
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
              {image.is_primary && (
                <Badge className="absolute top-4 left-4">
                  Primary
                </Badge>
              )}
            </CardContent>
          </Card>
        ))}

        <Card className="relative">
          <CardContent className="p-2">
            <label className="flex flex-col items-center justify-center w-full h-full aspect-square rounded-md border-2 border-dashed border-muted-foreground/25 hover:border-muted-foreground/50 cursor-pointer">
              <input
                type="file"
                className="hidden"
                multiple
                accept="image/*"
                onChange={handleUpload}
                disabled={uploading}
              />
              {uploading ? (
                <Loader2 className="h-8 w-8 animate-spin" />
              ) : (
                <>
                  <ImageIcon className="h-8 w-8 mb-2" />
                  <span className="text-sm text-muted-foreground">
                    Upload Images
                  </span>
                </>
              )}
            </label>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
