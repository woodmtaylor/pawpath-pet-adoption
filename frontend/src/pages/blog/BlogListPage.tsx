import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Search, Calendar, ArrowRight, ShoppingCart } from 'lucide-react';
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

export default function BlogListPage() {
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
      try {
          setLoading(true);
          const response = await api.get('/blog/posts');  // Make sure this is the correct endpoint
          console.log('Blog response:', response);
          
          if (response.data.success && response.data.data) {
              setPosts(response.data.data);
          }
      } catch (error) {
          console.error('Failed to fetch blog posts:', error);
      } finally {
          setLoading(false);
      }
  };

  const truncateContent = (content: string, maxLength: number = 150) => {
    if (!content) return '';
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength) + '...';
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Pet Care Blog</h1>
        <p className="text-muted-foreground">
          Tips, advice, and product recommendations for pet parents
        </p>
      </div>

      <div className="relative max-w-xl mb-8">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          placeholder="Search blog posts..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="pl-9"
        />
      </div>

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {posts.map((post) => (
          <Card key={post.post_id} className="flex flex-col">
            <CardHeader>
              <CardTitle className="line-clamp-2">{post.title}</CardTitle>
              <CardDescription className="flex items-center gap-2">
                <Calendar className="h-4 w-4" />
                {new Date(post.publication_date).toLocaleDateString()}
                <span className="text-sm">by {post.author_name}</span>
              </CardDescription>
            </CardHeader>
            <CardContent className="flex-1">
              <p className="text-muted-foreground mb-4">
                {truncateContent(post.content)}
              </p>
              {post.products && post.products.length > 0 && post.products.some(p => p !== null) && (
                <div className="mb-4">
                  <h4 className="text-sm font-medium mb-2">Featured Products:</h4>
                  <div className="flex flex-wrap gap-2">
                    {post.products.filter(Boolean).map((product) => (
                      <Button
                        key={product.product_id}
                        variant="outline"
                        size="sm"
                        className="flex items-center gap-1"
                      >
                        <ShoppingCart className="h-3 w-3" />
                        {product.name}
                      </Button>
                    ))}
                  </div>
                </div>
              )}
              <Button
                variant="link"
                className="p-0"
                onClick={() => navigate(`/blog/${post.post_id}`)}
              >
                Read More <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
