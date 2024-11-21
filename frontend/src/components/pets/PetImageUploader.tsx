import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Image as ImageIcon, X, Upload } from 'lucide-react';

interface PetImageUploaderProps {
  onChange: (files: File[]) => void;
  maxFiles?: number;
  accept?: string;
}

export function PetImageUploader({ 
  onChange, 
  maxFiles = 5, 
  accept = "image/*" 
}: PetImageUploaderProps) {
  const [previewUrls, setPreviewUrls] = useState<string[]>([]);

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    if (files.length + previewUrls.length > maxFiles) {
      alert(`You can only upload up to ${maxFiles} images`);
      return;
    }

    // Create preview URLs
    const newPreviewUrls = files.map(file => URL.createObjectURL(file));
    setPreviewUrls(prev => [...prev, ...newPreviewUrls]);
    
    // Call onChange with all files
    onChange(files);
  };

  const removeImage = (index: number) => {
    setPreviewUrls(prev => {
      const newUrls = [...prev];
      URL.revokeObjectURL(newUrls[index]); // Clean up URL object
      newUrls.splice(index, 1);
      return newUrls;
    });
    
    // Notify parent about removed image
    onChange([]);
  };

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {previewUrls.map((url, index) => (
          <Card key={url} className="relative group">
            <div className="aspect-square relative">
              <img
                src={url}
                alt={`Preview ${index + 1}`}
                className="w-full h-full object-cover rounded-md"
              />
              <Button
                size="icon"
                variant="destructive"
                className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                onClick={() => removeImage(index)}
              >
                <X className="h-4 w-4" />
              </Button>
            </div>
          </Card>
        ))}

        {previewUrls.length < maxFiles && (
          <Card className="aspect-square flex items-center justify-center cursor-pointer hover:bg-accent transition-colors">
            <label className="w-full h-full flex flex-col items-center justify-center cursor-pointer">
              <input
                type="file"
                className="hidden"
                accept={accept}
                multiple
                onChange={handleFileChange}
              />
              <Upload className="h-8 w-8 mb-2 text-muted-foreground" />
              <span className="text-sm text-muted-foreground">Upload Image</span>
            </label>
          </Card>
        )}
      </div>
    </div>
  );
}
