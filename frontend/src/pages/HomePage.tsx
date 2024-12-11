import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowRight, Heart, Search, Newspaper, PawPrint, Building2, Calendar } from 'lucide-react';

function HomePage() {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative py-20 bg-gradient-to-b from-primary/10 to-background">
        <div className="container mx-auto px-4">
          <div className="text-center max-w-3xl mx-auto">
            <h1 className="text-4xl md:text-6xl font-bold mb-6">
              Find Your Perfect Pet Companion
            </h1>
            <p className="text-xl text-muted-foreground mb-8">
              Take our matching quiz and discover the pet that fits perfectly with your lifestyle
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button size="lg" onClick={() => navigate('/quiz')}>
                Take the Quiz <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
              <Button size="lg" variant="outline" onClick={() => navigate('/pets')}>
                Browse Pets <Search className="ml-2 h-4 w-4" />
              </Button>
              <Button size="lg" variant="secondary" onClick={() => navigate('/shelters')}>
                View Shelters <Building2 className="ml-2 h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 bg-background">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">How PawPath Works</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Card>
              <CardContent className="pt-6">
                <div className="rounded-full bg-primary/10 w-12 h-12 flex items-center justify-center mb-4">
                  <Heart className="h-6 w-6 text-primary" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Personalized Matching</h3>
                <p className="text-muted-foreground">
                  Take our comprehensive quiz to find pets that match your lifestyle, living situation, and preferences.
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="pt-6">
                <div className="rounded-full bg-primary/10 w-12 h-12 flex items-center justify-center mb-4">
                  <Search className="h-6 w-6 text-primary" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Smart Search</h3>
                <p className="text-muted-foreground">
                  Browse pets from multiple shelters with advanced filters to find your perfect companion.
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="pt-6">
                <div className="rounded-full bg-primary/10 w-12 h-12 flex items-center justify-center mb-4">
                  <Newspaper className="h-6 w-6 text-primary" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Expert Resources</h3>
                <p className="text-muted-foreground">
                  Access our library of articles and guides about pet care, training, and adoption.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Recent Adoptables Section */}
      <section className="py-16 bg-secondary/50">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center mb-8">
            <h2 className="text-3xl font-bold">Recent Adoptables</h2>
            <Button variant="outline" onClick={() => navigate('/pets')}>
              View All <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* We'll fetch and display recent pets here later */}
            {[1, 2, 3].map((i) => (
              <Card key={i} className="relative group cursor-pointer" onClick={() => navigate('/pets')}>
                <CardContent className="p-0">
                  <div className="aspect-square bg-muted flex items-center justify-center">
                    <PawPrint className="h-12 w-12 text-muted-foreground" />
                  </div>
                  <div className="p-4">
                    <h3 className="font-semibold mb-1">Coming Soon</h3>
                    <p className="text-sm text-muted-foreground">Check back for new pets</p>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-primary text-primary-foreground">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Ready to Meet Your New Best Friend?
          </h2>
          <p className="text-xl mb-8 text-primary-foreground/90">
            Start your journey today and find the perfect pet companion.
          </p>
          <Button 
            size="lg" 
            variant="secondary"
            onClick={() => navigate('/quiz')}
          >
            Take the Quiz Now
            <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
        </div>
      </section>

      {/* Blog Section */}
      <section className="py-16 bg-background">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center mb-8">
            <div>
              <h2 className="text-3xl font-bold">Latest Pet Care Tips</h2>
              <p className="text-muted-foreground">
                Expert advice and recommendations for pet parents
              </p>
            </div>
            <Button variant="outline" onClick={() => navigate('/blog')}>
              View All <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Add 3 placeholder cards until we fetch real data */}
            {[1, 2, 3].map((i) => (
              <Card key={i} className="cursor-pointer" onClick={() => navigate('/blog')}>
                <CardContent className="p-6">
                  <div className="flex items-center gap-2 text-muted-foreground text-sm mb-3">
                    <Calendar className="h-4 w-4" />
                    {new Date().toLocaleDateString()}
                  </div>
                  <h3 className="font-semibold mb-2">Essential Pet Care Tips</h3>
                  <p className="text-muted-foreground line-clamp-2">
                    Learn how to provide the best care for your furry friends with our expert tips and recommendations.
                  </p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}

export default HomePage;
