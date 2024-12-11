import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ChevronLeft, Calendar, User, ShoppingCart } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import api from '@/lib/axios';

interface Product {
  product_id: number;
  name: string;
  description: string;
  price: number;
  affiliate_link: string;
}

interface BlogPost {
  post_id: number;
  title: string;
  content: string;
  publication_date: string;
  author_name: string;
  products?: Product[] | null;
}

export default function BlogDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [post, setPost] = useState<BlogPost | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPost();
  }, [id]);

  const fetchPost = async () => {
    try {
      setLoading(true);
      const response = await api.get(`/blog/posts/${id}`);
      console.log('Blog post response:', response);

      if (response.data.success) {
        setPost(response.data.data);
      } else {
        throw new Error(response.data.error || 'Failed to fetch post');
      }
    } catch (error) {
      console.error('Error:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "Failed to load blog post",
      });
      navigate('/blog/posts');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!post) {
    return (
      <div className="container max-w-4xl mx-auto p-6">
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-12">
            <h3 className="text-lg font-semibold mb-2">Post Not Found</h3>
            <p className="text-muted-foreground mb-4">
              The blog post you're looking for doesn't exist or has been removed.
            </p>
            <Button onClick={() => navigate('/blog/posts')}>
              Back to Blog
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="container max-w-4xl mx-auto p-6">
      <Button
        variant="ghost"
        className="mb-6"
        onClick={() => navigate('/blog')}
      >
        <ChevronLeft className="mr-2 h-4 w-4" />
        Back to Blog
      </Button>

      <Card>
        <CardHeader className="space-y-4">
          <CardTitle className="text-3xl">{post.title}</CardTitle>
          <div className="flex items-center gap-4 text-muted-foreground">
            <div className="flex items-center">
              <Calendar className="mr-2 h-4 w-4" />
              {new Date(post.publication_date).toLocaleDateString()}
            </div>
            <div className="flex items-center">
              <User className="mr-2 h-4 w-4" />
              {post.author_name}
            </div>
          </div>
        </CardHeader>
        <CardContent className="prose max-w-none">
          {post.content.split('\n').map((paragraph, index) => (
            <p key={index}>{paragraph}</p>
          ))}

          {post.products && post.products.length > 0 && post.products.some(p => p !== null) && (
            <div className="mt-8 border-t pt-8">
              <h2 className="text-xl font-semibold mb-4">Recommended Products</h2>
              <div className="grid gap-4 md:grid-cols-2">
                {post.products.filter(Boolean).map((product) => (
                  product && (
                    <Card key={product.product_id}>
                      <CardContent className="p-4">
                        <h3 className="font-semibold mb-2">{product.name}</h3>
                        <p className="text-sm text-muted-foreground mb-4">
                          {product.description}
                        </p>
                        <div className="flex justify-between items-center">
                          <span className="font-bold">${product.price}</span>
                          <Button variant="outline" className="flex items-center gap-2">
                            <ShoppingCart className="h-4 w-4" />
                            View Product
                          </Button>
                        </div>
                      </CardContent>
                    </Card>
                  )
                ))}
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
