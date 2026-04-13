import {
  Upload,
  FolderOpen,
  Search,
  Image,
  HardDrive,
  Clock,
} from 'lucide-react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
} from 'recharts';
import { useStorageAnalytics } from '../../features';
import {Loader, Error} from '../../shared';
import styles from '../../app/styles/Dashboard.module.css';

export function DashboardPage() {
  const { data, loading, error } = useStorageAnalytics();

  function formatBytes(bytes: number) {
    if (!bytes) return '0 MB';

    const mb = bytes / (1024 * 1024);
    const gb = bytes / (1024 * 1024 * 1024);

    if (gb >= 1) return `${gb.toFixed(2)} GB`;
    return `${mb.toFixed(2)} MB`;
  }

  const navigation = [
    { name: 'Загрузка', path: '/upload', icon: <Upload /> },
    { name: 'Архив', path: '/archive', icon: <FolderOpen /> },
    { name: 'Поиск', path: '/search', icon: <Search /> },
  ];

  const photoCount = data?.photoCount ?? 0;
  const usedBytes = data?.usedBytes ?? 0;
  const timeline = data?.timeline ?? [];

  if (loading) {
    return <Loader/>;
  }

  if (error) {
    return <Error error={error}/>;
  }

  return (
    <div className={styles.dashboardPage}>
      <div className={styles.dashboardContent}>
        <motion.div
          className={styles.dashboardWelcome}
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1>Панель управления</h1>
          <p>Добро пожаловать на вашу панель управления!</p>
        </motion.div>

        <div className={styles.dashboardNav}>
          {navigation.map((item) => (
            <Link key={item.path} to={item.path} className={styles.dashboardNavItem}>
              <div className={styles.dashboardNavItemIcon}>{item.icon}</div>
              <h2>{item.name}</h2>
            </Link>
          ))}
        </div>

        <div className={styles.dashboardStats}>
          <motion.div
            className={styles.dashboardStatsItem}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
          >
            <div className={styles.dashboardStatsHeader}>
              <div className={styles.dashboardStatsIcon}>
                <Image />
              </div>
              <div className={styles.dashboardStatsInfo}>
                <h2>Общее кол-во фото</h2>
              </div>
            </div>
            <div className={styles.dashboardStatsBody}>
              <div className={styles.dashboardStatsNumber}>{photoCount}</div>
              <div className={styles.dashboardStatsComment}>
                <p>Файлов в хранилище</p>
              </div>
            </div>
          </motion.div>

          <motion.div
            className={styles.dashboardStatsItem}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
          >
            <div className={styles.dashboardStatsHeader}>
              <div className={styles.dashboardStatsIcon}>
                <HardDrive />
              </div>
              <div className={styles.dashboardStatsInfo}>
                <h2>Занятое пространство</h2>
              </div>
            </div>
            <div className={styles.dashboardStatsBody}>
              <div className={styles.dashboardStatsNumber}>
                {formatBytes(usedBytes)}
              </div>
              <div className={styles.dashboardStatsComment}>
                <p>Используется из 50 GB</p>
              </div>
            </div>
          </motion.div>
        </div>
        <div className={styles.dashboardStorage}>
          <motion.div
            className={styles.dashboardStorageTimeline}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
          >
            <div className={styles.dashboardStorageTimelineHeader}>
              <Clock />
              <h2>Таймлайн загрузок</h2>
            </div>

            <ResponsiveContainer width="100%" height={250} >
              <AreaChart data={timeline} margin={{ top: 40 }}>
                <defs>
                  <linearGradient
                    id="storageGradient"
                    x1="0"
                    y1="0"
                    x2="0"
                    y2="1"
                  >
                    <stop offset="5%" stopColor="#06b6d4" stopOpacity={0.8} />
                    <stop offset="95%" stopColor="#06b6d4" stopOpacity={0} />
                  </linearGradient>
                </defs>

                <XAxis dataKey="month" stroke="#94a3b8" />
                <YAxis stroke="#94a3b8" />
                <Tooltip />

                <Area
                  type="monotone"
                  dataKey="storage"
                  stroke="#06b6d4"
                  fill="url(#storageGradient)"
                />
              </AreaChart>
            </ResponsiveContainer>
          </motion.div>
        </div>
      </div>
    </div>
  );
}