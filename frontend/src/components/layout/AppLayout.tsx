import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard,
  Users as UsersIcon,
  Workflow,
  ClipboardCheck,
  Building2,
  Package,
  FileText,
  ShoppingCart,
  LogOut,
  Sparkles,
} from 'lucide-react';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const NAV = [
  {
    group: 'Overview',
    items: [{ to: '/', label: 'Dashboard', icon: LayoutDashboard, end: true }],
  },
  {
    group: 'ERP',
    items: [
      { to: '/customers', label: 'Customers', icon: Building2 },
      { to: '/products', label: 'Products & Stock', icon: Package },
      { to: '/purchase-requests', label: 'Purchase Requests', icon: ShoppingCart },
      { to: '/invoices', label: 'Invoices', icon: FileText },
      { to: '/users', label: 'Users & Roles', icon: UsersIcon },
    ],
  },
  {
    group: 'Automation',
    items: [
      { to: '/workflows', label: 'Workflows', icon: Workflow },
      { to: '/approvals', label: 'Approvals', icon: ClipboardCheck },
    ],
  },
];

export function AppLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  return (
    <div className="flex min-h-screen">
      <aside className="flex w-64 flex-col border-r bg-card">
        <div className="flex h-16 items-center gap-2 border-b px-6">
          <Sparkles className="h-5 w-5 text-primary" />
          <span className="text-lg font-bold tracking-tight text-primary">FlowForge</span>
        </div>

        <nav className="flex-1 space-y-6 overflow-y-auto px-4 py-5">
          {NAV.map((section) => (
            <div key={section.group}>
              <p className="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                {section.group}
              </p>
              <div className="space-y-1">
                {section.items.map((item) => (
                  <NavLink
                    key={item.to}
                    to={item.to}
                    end={'end' in item ? item.end : undefined}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        isActive
                          ? 'bg-accent text-accent-foreground'
                          : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                      )
                    }
                  >
                    <item.icon className="h-4 w-4" />
                    {item.label}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
        </nav>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="flex h-16 items-center justify-end gap-4 border-b bg-card px-6">
          <div className="text-right">
            <p className="text-sm font-medium leading-none">{user?.name}</p>
            <p className="mt-1 text-xs capitalize text-muted-foreground">
              {user?.roles?.join(', ') ?? '—'}
            </p>
          </div>
          <Button
            variant="outline"
            size="sm"
            onClick={() => {
              void logout().then(() => navigate('/login'));
            }}
          >
            <LogOut className="h-4 w-4" />
            <span className="sr-only">Log out</span>
          </Button>
        </header>
        <main className="flex-1 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}