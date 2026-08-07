import { Construction } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

export function ModulePlaceholder({
  title,
  description,
  phase,
}: {
  title: string;
  description: string;
  phase: string;
}) {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
        <p className="text-muted-foreground">{description}</p>
      </div>

      <Card className="mx-auto max-w-xl">
        <CardHeader className="items-center text-center">
          <div className="mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-accent-foreground">
            <Construction className="h-6 w-6" />
          </div>
          <CardTitle>Coming soon</CardTitle>
          <CardDescription>
            This module is scheduled for <strong>{phase}</strong>. The navigation entry is already
            wired and will light up once the backend endpoint is available.
          </CardDescription>
        </CardHeader>
        <CardContent className="flex justify-center">
          <Button variant="outline" disabled>
            Not available yet
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}