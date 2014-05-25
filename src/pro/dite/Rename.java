package pro.dite;

import java.io.Serializable;

public class Rename implements Serializable
{
    String from;
    String to;

    public Rename(String from, String to)
    {
        this.from = from;
        this.to = to;
    }
}

