import { LayoutDashboard, Upload, FolderOpen, Search, Cloud } from 'lucide-react';
import { useStorageAnalytics } from '../../../features';
import { Link } from 'react-router-dom';
import '../../../app/styles/SideBar.css';

export function SideBar() {
  const { data, loading, error } = useStorageAnalytics();
  function formatBytes(bytes: number) {
    if (bytes === 0) return '0 MB';

    const mb = bytes / (1024 * 1024);
    const gb = bytes / (1024 * 1024 * 1024);

    if (gb >= 1) {
      return `${gb.toFixed(2)} GB`;
    }

    return `${mb.toFixed(2)} MB`;
  }
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
            {loading && <p>Загрузка...</p>}
            {error && <p className="error">{error}</p>}
            {!loading && !error && data && (
              <>
                <div className="storage-info__stats">
                  <span className="storage-info-stats__text">
                    {formatBytes(data.usedBytes)} из {formatBytes(data.totalBytes)}
                  </span>

                  <span className="storage-info-stats__percent">
                    {data.percent > 0 && data.percent < 0.01
                      ? '< 0.01%'
                      : `${data.percent.toFixed(2)}%`}
                  </span>
                </div>

                <div className="storage-info__bar">
                  <div
                    className="storage-info-bar__progress"
                    style={{
                      width:
                        data.percent > 0 ? `${data.percent}%` : data.usedBytes > 0 ? '1%' : '0%',
                    }}
                  />
                </div>
                <p className="storage-info__note">Статистика обновляется автоматически</p>
              </>
            )}
          </div>
        </div>
      </div>
    </aside>
  );
}
