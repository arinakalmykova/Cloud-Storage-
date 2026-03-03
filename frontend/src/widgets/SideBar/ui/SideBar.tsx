import { LayoutDashboard, Upload, FolderOpen, Search, Cloud } from 'lucide-react';
import { Link } from 'react-router-dom';
import '../../../app/styles/SideBar.css';

export function SideBar() {
  const navigation = [
    { name: 'Панель управления', path: '/', icon: LayoutDashboard },
    { name: 'Загрузка', path: '/upload', icon: Upload },
    { name: 'Архив', path: '/archive', icon: FolderOpen },
    { name: 'Поиск', path: '/search', icon: Search },
  ];

  return (
    <aside className="sidebar">
      <div className="sidebar-content">
        <nav>
          <ul className="sidebar-menu">
            {navigation.map((item) => (
              <li key={item.path} className="sidebar-item">
                <Link to={item.path} className="sidebar-link">
                  <item.icon className="sidebar-icon" />
                  <span className="sidebar-text">{item.name}</span>
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        <div className="storage-info">
          <div className="storage-info__header">
            <Cloud className="storage-info__icon" />
            <span className="storage-info__text">Хранилище</span>
          </div>
          <div className="storage-info__body">
            <div className="storage-info__stats">
              <span className="storage-info-stats__text">68 GB из 100 GB</span>
              <span className="storage-info-stats__percent">68%</span>
            </div>
            <div className="storage-info__bar">
              <div className="storage-info-bar__progress" />
            </div>
            <p className="storage-info__note">12.4 GB сохранено за последний месяц</p>
          </div>
        </div>
      </div>
    </aside>
  );
}
