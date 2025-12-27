import { NavLink } from 'react-router-dom';
import {
  LayoutDashboard,
  Key,
  BarChart3,
  Settings,
  Shield,
  Webhook,
  FileText,
  Users,
  Package,
} from 'lucide-react';

const navigation = [
  { name: 'Dashboard', to: '/', icon: LayoutDashboard },
  { name: 'Licenses', to: '/licenses', icon: Key },
  { name: 'Analytics', to: '/analytics', icon: BarChart3 },
  { name: 'Audit Trail', to: '/audit', icon: FileText },
  { name: 'Webhooks', to: '/webhooks', icon: Webhook },
  { name: 'Products', to: '/products', icon: Package },
  { name: 'GDPR Tools', to: '/gdpr', icon: Users },
  { name: 'Security', to: '/security', icon: Shield },
  { name: 'Settings', to: '/settings', icon: Settings },
];

export default function Sidebar() {
  return (
    <aside className="w-56 bg-slate-900 min-h-screen flex flex-col">
      {/* Logo */}
      <div className="px-4 py-5 border-b border-slate-800">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
            <Key className="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 className="text-white font-bold text-lg leading-tight">License</h1>
            <p className="text-slate-400 text-xs">Server</p>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 px-3 py-4 space-y-1">
        {navigation.map((item) => {
          const Icon = item.icon;
          return (
            <NavLink
              key={item.name}
              to={item.to}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                  isActive
                    ? 'bg-primary-600 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                }`
              }
            >
              <Icon className="w-5 h-5" />
              {item.name}
            </NavLink>
          );
        })}
      </nav>

      {/* Footer */}
      <div className="px-4 py-4 border-t border-slate-800">
        <p className="text-xs text-slate-500">Peanut License Server v1.2.9</p>
      </div>
    </aside>
  );
}
