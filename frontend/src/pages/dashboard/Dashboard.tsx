import {
  Upload,
  FolderOpen,
  Search,
  Image,
  HardDrive,
  Clock,
  Award,
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
import '../../app/styles/Dashboard.css';

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
    { name: 'Архив', path: '/files', icon: <FolderOpen /> },
    { name: 'Поиск', path: '/search', icon: <Search /> },
  ];

  const photoCount = data?.photoCount ?? 0;
  const usedBytes = data?.usedBytes ?? 0;
  const uploadSpeed = data?.uploadSpeed ?? '—';
  const timeline = data?.timeline ?? [];

  if (loading) {
    return <div className="dashboard-page">Загрузка...</div>;
  }

  if (error) {
    return <div className="dashboard-page">Ошибка: {error}</div>;
  }

  return (
    <div className="dashboard-page">
      <div className="dashboard__content">
        <motion.div
          className="dashboard__welcome"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1>Панель управления</h1>
          <p>Добро пожаловать на вашу панель управления!</p>
        </motion.div>

        <div className="dashboard__nav">
          {navigation.map((item) => (
            <Link key={item.path} to={item.path} className="dashboard__nav-item">
              <div className="dashboard__nav-icon">{item.icon}</div>
              <h2>{item.name}</h2>
            </Link>
          ))}
        </div>

        <div className="dashboard__stats">
          <motion.div
            className="dashboard__stats-item"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
          >
            <div className="dashboard__stats__header">
              <div className="dashboard__stats-icon">
                <Image />
              </div>
              <div className="dashboard__stats-info">
                <h2>Общее кол-во фото</h2>
              </div>
            </div>
            <div className="dashboard__stats__body">
              <div className="dashboard__stats__number">{photoCount}</div>
              <div className="dashboard__stats__comment">
                <p>Файлов в хранилище</p>
              </div>
            </div>
          </motion.div>

          <motion.div
            className="dashboard__stats-item"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
          >
            <div className="dashboard__stats__header">
              <div className="dashboard__stats-icon">
                <HardDrive />
              </div>
              <div className="dashboard__stats-info">
                <h2>Занятое пространство</h2>
              </div>
            </div>
            <div className="dashboard__stats__body">
              <div className="dashboard__stats__number">
                {formatBytes(usedBytes)}
              </div>
              <div className="dashboard__stats__comment">
                <p>Используется из 100 GB</p>
              </div>
            </div>
          </motion.div>

          <motion.div
            className="dashboard__stats-item"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
          >
            <div className="dashboard__stats__header">
              <div className="dashboard__stats-icon">
                <Award />
              </div>
              <div className="dashboard__stats-info">
                <h2>Скорость загрузки</h2>
              </div>
            </div>
            <div className="dashboard__stats__body">
              <div className="dashboard__stats__number">{uploadSpeed}</div>
              <div className="dashboard__stats__comment">
                <p>Оценка производительности</p>
              </div>
            </div>
          </motion.div>
        </div>
        <div className="dashboard__storage">
          <motion.div
            className="dashboard__storage-timeline"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
          >
            <div className="dashboard__storage-timeline__header">
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