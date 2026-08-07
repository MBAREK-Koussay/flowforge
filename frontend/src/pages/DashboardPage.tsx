import { Link } from 'react-router-dom';
import {
  Workflow,
  ClipboardCheck,
  Users,
  Building2,
  Play,
  CheckCircle2,
} from 'lucide-react';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

const KPI_CARDS = [
  { label: 'Total Workflows', value: '—', icon: Workflow, hint: 'via Workflows module' },
  { label: 'Running', value: '—', icon: Play, hint: 'via execution engine' },
  { label: 'Completed', value: '—', icon: CheckCircle2, hint: 'via execution engine' },
  { label: 'Pending Approvals', value: '—', icon: ClipboardCheck, hint: 'via approvals module' },
];

export function DashboardPage() {
  const { user } = useAuth();

  const initials = (user?.name ?? '?')
    .split(' ')
    .map((p) => p[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">
          Welcome back, {user?.name?.split(' ')[0] ?? 'there'}
        </h1>
        <p className="text-muted-foreground">Overview of your FlowForge workspace.</p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {KPI_CARDS.map((item) => (
          <Card key={item.label}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                {item.label}
              </CardTitle>
              <item.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{item.value}</div>
              <p className="text-xs text-muted-foreground">{item.hint}</p>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Getting started</CardTitle>
            <CardDescription>Build your ERP automation step by step.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            <ActionRow
              icon={Workflow}
              title="Build your first workflow"
              description="Visual workflow builder (React Flow) lands in Phase 4."
              to="/workflows"
              cta="Get started"
            />
            <ActionRow
              icon={Building2}
              title="Load your business data"
              description="Customers, products, purchase requests and invoices arrive in Phase 2."
              to="/customers"
              cta="Browse"
            />
            <ActionRow
              icon={Users}
              title="Manage your team"
              description="Roles and permissions already live in the backend."
              to="/users"
              cta="Manage users"
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Profile</CardTitle>
            <CardDescription>Your account details</CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col items-center gap-3 text-center">
            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground">
              {initials}
            </div>
            <div>
              <p className="font-semibold">{user?.name}</p>
              <p className="text-sm text-muted-foreground">{user?.email}</p>
            </div>
            <div className="flex flex-wrap justify-center gap-1.5">
              {(user?.roles ?? []).map((role) => (
                <span
                  key={role}
                  className="inline-flex items-center rounded-full bg-accent px-2.5 py-0.5 text-xs font-medium capitalize text-accent-foreground"
                >
                  {role}
                </span>
              ))}
            </div>
            <Link to="/approvals" className="mt-2 w-full">
              <Button variant="outline" className="w-full">
                <ClipboardCheck className="h-4 w-4" />
                Pending approvals
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function ActionRow({
  icon: Icon,
  title,
  description,
  to,
  cta,
}: {
  icon: React.ComponentType<{ className?: string }>;
  title: string;
  description: string;
  to: string;
  cta: string;
}) {
  return (
    <div className="flex items-center justify-between rounded-lg border p-3">
      <div className="flex items-center gap-3">
        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-accent text-accent-foreground">
          <Icon className="h-4 w-4" />
        </div>
        <div>
          <p className="font-medium">{title}</p>
          <p className="text-sm text-muted-foreground">{description}</p>
        </div>
      </div>
      <Link to={to}>
        <Button variant="ghost" size="sm">
          {cta} →
        </Button>
      </Link>
    </div>
  );
}