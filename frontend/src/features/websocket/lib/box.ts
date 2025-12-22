// src/Box.ts
export class Box {
  boxId: string;

  constructor(boxId: string) {
    this.boxId = boxId;
  }

  // Отправка сообщения в чужой ящик
  async sendMessage(peerBoxId: string, msg: any) {
    await fetch(
      `http://localhost/reverb/src/box.php?box=${peerBoxId}&msg=${encodeURIComponent(JSON.stringify(msg))}`
    );
  }

  // Получение сообщений из своего ящика
  async receiveMessages(onMessage: (msg: any) => void) {
    const res = await fetch(`http://localhost/reverb/src/box.php?box=${this.boxId}`);
    const data = await res.json();

    if (data.type === 'msgs') {
      for (const msgStr of data.data) {
        onMessage(JSON.parse(msgStr));
      }
    }
  }
}
