import { LayoutDashboard, Upload, FolderOpen, Search, Cloud } from 'lucide-react';
import { useStorageAnalytics } from '../../../features';
import { Loader, Error } from '../../../shared';
import { Link } from 'react-router-dom';
import styles from '../../../app/styles/SideBar.module.css';

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
    <aside className={styles.sidebar}>
      <div className={styles.sidebarContent}>
        <nav>
          <ul className={styles.sidebarMenu}>
            {navigation.map((item) => (
              <li key={item.path} className={styles.sidebarItem}>
                <Link to={item.path} className={styles.sidebarLink}>
                  <item.icon className={styles.sidebarIcon} />
                  <span className={styles.sidebarText}>{item.name}</span>
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        <div className={styles.storageInfo}>
          <div className={styles.storageInfoHeader}>
            <Cloud className={styles.storageInfoIcon} />
            <span className={styles.storageInfoText}>Хранилище</span>
          </div>
          <div className={styles.storageInfoBody}>
            {loading && <Loader />}
            {error && <Error error={error} />}
            {!loading && !error && data && (
              <>
                <div className={styles.storageInfoStats}>
                  <span className={styles.storageInfoStatsText}>
                    {formatBytes(data.usedBytes)} из {formatBytes(data.totalBytes)}
                  </span>

                  <span className={styles.storageInfoStatsPercent}>
                    {data.percent > 0 && data.percent < 0.01
                      ? '< 0.01%'
                      : `${data.percent.toFixed(2)}%`}
                  </span>
                </div>

                <div className={styles.storageInfoBar}>
                  <div
                    className={styles.storageInfoBarProgress}
                    style={{
                      width:
                        data.percent > 0 && data.percent < 1
                          ? '1%'
                          : `${data.percent}%`,
                    }}
                  />
                </div>
                <p className={styles.storageInfoNote}>Статистика обновляется автоматически</p>
              </>
            )}
          </div>
        </div>
      </div>
    </aside>
  );
}
